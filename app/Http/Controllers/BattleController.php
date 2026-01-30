<?php

namespace App\Http\Controllers;

use App\Models\Battle;
use App\Models\BattlePlayer;
use App\Models\Team;
use App\Services\ShowdownIntegration;
use App\Services\MatchmakingService;
use App\Services\PvEAIService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BattleController extends Controller
{
    protected ShowdownIntegration $showdownIntegration;
    protected MatchmakingService $matchmakingService;
    protected PvEAIService $pvEAIService;

    public function __construct(
        ShowdownIntegration $showdownIntegration,
        MatchmakingService $matchmakingService,
        PvEAIService $pvEAIService
    ) {
        $this->showdownIntegration = $showdownIntegration;
        $this->matchmakingService = $matchmakingService;
        $this->pvEAIService = $pvEAIService;

        $this->middleware('auth');
    }

    /**
     * Get list of battles for the authenticated user.
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();
        $battles = $user->battlePlayers()
            ->with('battle', 'team', 'battle.players.user')
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $battles,
        ]);
    }

    /**
     * Get details of a specific battle.
     */
    public function show(Battle $battle): JsonResponse
    {
        $this->authorize('view', $battle);

        $battleData = [
            'id' => $battle->id,
            'type' => $battle->type,
            'format' => $battle->format,
            'status' => $battle->status,
            'players' => $battle->players()->with('user', 'team')->get(),
            'turns' => $battle->turnDecisions()
                ->orderBy('turn_number')
                ->with('battlePlayer')
                ->get(),
            'winner' => $battle->winner,
            'replayLog' => $battle->replay_log,
        ];

        return response()->json([
            'success' => true,
            'data' => $battleData,
        ]);
    }

    /**
     * Create a PVP battle with matchmaking.
     */
    public function createPvp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'team_id' => 'required|exists:teams,id',
            'format' => 'required|in:singles,doubles',
        ]);

        $user = auth()->user();
        $team = Team::findOrFail($validated['team_id']);

        // Verify team belongs to user
        if ($team->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Team does not belong to you',
            ], 403);
        }

        // Find opponent
        $opponent = $this->matchmakingService->findRandomOpponent($user, $team);

        if (!$opponent) {
            return response()->json([
                'success' => false,
                'message' => 'No opponents available',
            ], 404);
        }

        $opponentTeam = $opponent->teams()->first();

        if (!$opponentTeam) {
            return response()->json([
                'success' => false,
                'message' => 'Opponent has no teams',
            ], 404);
        }

        try {
            // Create battle in database
            $battle = $this->matchmakingService->createPvpBattle(
                $user,
                $team,
                $opponent,
                $opponentTeam,
                $validated['format']
            );

            // Initialize on Showdown microservice
            $p1 = $battle->getPlayerBySlot('p1');
            $p2 = $battle->getPlayerBySlot('p2');
            $this->showdownIntegration->createBattle($battle, $p1, $p2);

            $battle->status = 'active';
            $battle->save();

            // Broadcast battle started event
            broadcast(new \App\Events\BattleStarted($battle))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Battle created successfully',
                'data' => $battle->load('players.user', 'players.team'),
            ], 201);
        } catch (\Exception $e) {
            \Log::error("Battle creation failed: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to create battle: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a PVE battle.
     */
    public function createPve(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'team_id' => 'required|exists:teams,id',
            'difficulty' => 'required|in:easy,normal,hard',
        ]);

        $user = auth()->user();
        $team = Team::findOrFail($validated['team_id']);

        // Verify team belongs to user
        if ($team->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Team does not belong to you',
            ], 403);
        }

        try {
            // Create PvE battle
            $battle = $this->pvEAIService->createPvEBattle($user, $team, $validated['difficulty']);

            // Initialize on Showdown microservice
            $p1 = $battle->getPlayerBySlot('p1');
            $p2 = $battle->getPlayerBySlot('p2');

            // For AI, generate a team
            $this->showdownIntegration->createBattle($battle, $p1, $p2);

            $battle->status = 'active';
            $battle->save();

            broadcast(new \App\Events\BattleStarted($battle))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'PvE battle created successfully',
                'data' => $battle->load('players.user', 'players.team'),
            ], 201);
        } catch (\Exception $e) {
            \Log::error("PvE battle creation failed: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to create battle: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submit a move in battle.
     */
    public function submitMove(Request $request, Battle $battle): JsonResponse
    {
        $this->authorize('participate', $battle);

        $validated = $request->validate([
            'move' => 'required|string',
        ]);

        try {
            $user = auth()->user();
            $battlePlayer = $battle->players()->where('user_id', $user->id)->first();

            if (!$battlePlayer) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not part of this battle',
                ], 403);
            }

            // Record decision in database
            $turnNumber = $battle->turnDecisions()
                ->where('battle_player_id', $battlePlayer->id)
                ->max('turn_number') + 1;

            $decision = $battlePlayer->recordDecision(
                $turnNumber,
                'move',
                ['move' => $validated['move']]
            );

            // Build move action
            $p1Action = \App\Services\ShowdownIntegration::buildAction('move', $validated['move']);
            $p2Action = '>move 1'; // Default

            if ($battlePlayer->player_slot === 'p2') {
                $p2Action = $p1Action;
                $p1Action = '>move 1';
            }

            // Get other player
            $opponent = $battlePlayer->getOpponent();
            if (!$opponent) {
                throw new \Exception("Opponent not found");
            }

            // Submit to Showdown
            $result = $this->showdownIntegration->submitTurn(
                $battle,
                $battle->getPlayerBySlot('p1'),
                $battle->getPlayerBySlot('p2'),
                $p1Action,
                $p2Action
            );

            $decision->markExecuted($result);

            // If PvE, have AI make a move
            if ($battle->type === 'pve') {
                $aiPlayer = $battle->players()->where('is_ai', true)->first();
                $this->pvEAIService->executeTurn($battle, $aiPlayer);
            }

            // Broadcast turn event
            broadcast(new \App\Events\TurnResolved($battle, $battlePlayer))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Move submitted successfully',
                'data' => [
                    'decision' => $decision,
                    'result' => $result,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error("Move submission failed: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit move: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Switch a Pokémon in battle.
     */
    public function switchPokemon(Request $request, Battle $battle): JsonResponse
    {
        $this->authorize('participate', $battle);

        $validated = $request->validate([
            'pokemon_index' => 'required|integer|min:0|max:5',
        ]);

        try {
            $user = auth()->user();
            $battlePlayer = $battle->players()->where('user_id', $user->id)->first();

            if (!$battlePlayer) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not part of this battle',
                ], 403);
            }

            // Record decision
            $turnNumber = $battle->turnDecisions()
                ->where('battle_player_id', $battlePlayer->id)
                ->max('turn_number') + 1;

            $decision = $battlePlayer->recordDecision(
                $turnNumber,
                'switch',
                ['pokemonIndex' => $validated['pokemon_index']]
            );

            // Build switch action
            $p1Action = \App\Services\ShowdownIntegration::buildAction('switch', $validated['pokemon_index']);
            $p2Action = '>move 1'; // Default

            if ($battlePlayer->player_slot === 'p2') {
                $p2Action = $p1Action;
                $p1Action = '>move 1';
            }

            // Submit to Showdown
            $result = $this->showdownIntegration->submitTurn(
                $battle,
                $battle->getPlayerBySlot('p1'),
                $battle->getPlayerBySlot('p2'),
                $p1Action,
                $p2Action
            );
            $decision->markExecuted($result);

            broadcast(new \App\Events\TurnResolved($battle, $battlePlayer))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Pokémon switched successfully',
                'data' => [
                    'decision' => $decision,
                    'result' => $result,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error("Switch Pokémon failed: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to switch Pokémon: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get current battle state.
     */
    public function getState(Battle $battle): JsonResponse
    {
        $this->authorize('view', $battle);

        try {
            $state = $this->showdownIntegration->getBattleState($battle);

            return response()->json([
                'success' => true,
                'data' => $state,
            ]);
        } catch (\Exception $e) {
            \Log::error("Get battle state failed: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to get battle state: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Forfeit battle.
     */
    public function forfeit(Battle $battle): JsonResponse
    {
        $this->authorize('participate', $battle);

        try {
            $user = auth()->user();
            $battlePlayer = $battle->players()->where('user_id', $user->id)->first();

            if (!$battlePlayer) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not part of this battle',
                ], 403);
            }

            // Forfeit on Showdown
            $opponent = $battlePlayer->getOpponent();
            if (!$opponent) {
                throw new \Exception("Opponent not found");
            }

            $winner = $opponent->player_slot === 'p1' ? 'p1' : 'p2';
            $this->showdownIntegration->finishBattle($battle, $winner);

            // Mark opponent as winner
            $battle->finish($opponent->user_id);

            // Update battle player records
            $battlePlayer->is_winner = false;
            $battlePlayer->save();

            if ($opponent) {
                $opponent->is_winner = true;
                $opponent->save();
            }

            broadcast(new \App\Events\BattleFinished($battle))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Battle forfeited',
            ]);
        } catch (\Exception $e) {
            \Log::error("Forfeit failed: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to forfeit: ' . $e->getMessage(),
            ], 500);
        }
    }
}
