<?php

namespace App\Services;

use App\Models\Battle;
use App\Models\BattlePlayer;
use Illuminate\Support\Arr;

class PvEAIService
{
    private ShowdownClient $showdownClient;

    public function __construct(ShowdownClient $showdownClient)
    {
        $this->showdownClient = $showdownClient;
    }

    /**
     * Generate a random move for the AI.
     */
    public function generateRandomMove(Battle $battle, BattlePlayer $aiPlayer): string
    {
        $battleState = $this->showdownClient->getBattleState($battle);

        // Get AI's active Pokémon
        $activePokemon = $battleState['activePokemons']['p2'] ?? null;

        if (!$activePokemon || !isset($activePokemon['moves'])) {
            return 'Struggle'; // Fallback move
        }

        $availableMoves = array_filter($activePokemon['moves'], fn($move) => $move['pp'] > 0);

        if (empty($availableMoves)) {
            return 'Struggle';
        }

        // Randomly pick a move
        $selectedMove = Arr::random($availableMoves);
        return $selectedMove['name'];
    }

    /**
     * Generate an intelligent move based on battle state.
     */
    public function generateIntelligentMove(Battle $battle, BattlePlayer $aiPlayer, string $difficulty = 'normal'): string
    {
        $battleState = $this->showdownClient->getBattleState($battle);
        $activePokemon = $battleState['activePokemons']['p2'] ?? null;
        $opponentPokemon = $battleState['activePokemons']['p1'] ?? null;

        if (!$activePokemon || !isset($activePokemon['moves'])) {
            return 'Struggle';
        }

        $availableMoves = array_filter($activePokemon['moves'], fn($move) => $move['pp'] > 0);

        if (empty($availableMoves)) {
            return 'Struggle';
        }

        // Very basic strategy: prefer super-effective moves
        $moveNames = array_map(fn($m) => $m['name'], $availableMoves);

        // For a more sophisticated AI, we would:
        // 1. Analyze opponent type matchups
        // 2. Evaluate move effectiveness
        // 3. Consider switching strategy
        // For now, just return a random move

        return Arr::random($moveNames);
    }

    /**
     * Decide whether AI should switch Pokémon.
     */
    public function shouldSwitch(Battle $battle, BattlePlayer $aiPlayer): bool
    {
        $battleState = $this->showdownClient->getBattleState($battle);
        $activePokemon = $battleState['activePokemons']['p2'] ?? null;

        if (!$activePokemon) {
            return false;
        }

        // Switch if current Pokémon is low on HP (less than 25%)
        $hpPercentage = ($activePokemon['hp'] ?? 0) / ($activePokemon['maxHp'] ?? 1);
        return $hpPercentage < 0.25;
    }

    /**
     * Get the next switch target index.
     */
    public function getNextSwitchTarget(Battle $battle, BattlePlayer $aiPlayer): int
    {
        $battleState = $this->showdownClient->getBattleState($battle);
        $team = $battleState['teams']['p2'] ?? [];

        // Find first alive Pokémon that's not the current active one
        foreach ($team as $index => $pokemon) {
            if ($pokemon['status'] !== 'fainted') {
                return $index;
            }
        }

        return 0; // Fallback
    }

    /**
     * Execute AI turn.
     */
    public function executeTurn(Battle $battle, BattlePlayer $aiPlayer): void
    {
        try {
            // Decide action
            if ($this->shouldSwitch($battle, $aiPlayer)) {
                $targetIndex = $this->getNextSwitchTarget($battle, $aiPlayer);
                $this->showdownClient->switchPokemon($battle, 'p2', $targetIndex);
            } else {
                $move = $this->generateIntelligentMove($battle, $aiPlayer, 'normal');
                $this->showdownClient->submitMove($battle, 'p2', $move);
            }
        } catch (\Exception $e) {
            \Log::error("AI turn execution failed: {$e->getMessage()}");
        }
    }

    /**
     * Create a PvE battle.
     */
    public function createPvEBattle(\App\Models\User $player, \App\Models\Team $team, string $difficulty = 'normal'): Battle
    {
        // Create battle
        $battle = Battle::create([
            'type' => 'pve',
            'format' => 'singles',
            'status' => 'pending',
        ]);

        // Add human player
        BattlePlayer::create([
            'battle_id' => $battle->id,
            'user_id' => $player->id,
            'team_id' => $team->id,
            'player_slot' => 'p1',
            'is_ai' => false,
        ]);

        // Create AI team (simple: generate from available Pokémon)
        $aiTeam = $this->generateAITeam($difficulty);

        // Add AI player
        BattlePlayer::create([
            'battle_id' => $battle->id,
            'user_id' => null,
            'team_id' => null,
            'player_slot' => 'p2',
            'is_ai' => true,
        ]);

        return $battle;
    }

    /**
     * Generate AI team based on difficulty.
     */
    private function generateAITeam(string $difficulty = 'normal'): array
    {
        // TODO: Implement team generation based on difficulty
        // For now, return a simple team structure
        return [
            [
                'name' => 'Pikachu',
                'level' => $difficulty === 'hard' ? 60 : 50,
                'moves' => ['Thunderbolt', 'Quick Attack', 'Thunder Wave', 'Iron Tail'],
            ],
            [
                'name' => 'Blastoise',
                'level' => $difficulty === 'hard' ? 60 : 50,
                'moves' => ['Hydro Pump', 'Ice Beam', 'Earthquake', 'Recover'],
            ],
        ];
    }
}
