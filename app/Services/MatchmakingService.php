<?php

namespace App\Services;

use App\Models\Battle;
use App\Models\BattlePlayer;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class MatchmakingService
{
    /**
     * Find a random opponent for PVP battles.
     */
    public function findRandomOpponent(User $player, Team $team): ?User
    {
        // Get random user excluding the player
        return User::where('id', '!=', $player->id)
            ->inRandomOrder()
            ->first();
    }

    /**
     * Find opponents within a certain rating range (future implementation).
     */
    public function findOpponentByRating(User $player, int $ratingRange = 100): ?User
    {
        // TODO: Implement rating-based matchmaking
        // For now, just find a random opponent
        return $this->findRandomOpponent($player, $player->teams()->first());
    }

    /**
     * Create a PVP battle between two players.
     */
    public function createPvpBattle(User $player1, Team $team1, User $player2, Team $team2, string $format = 'singles'): Battle
    {
        // Create the battle
        $battle = Battle::create([
            'type' => 'pvp',
            'format' => $format,
            'status' => 'pending',
        ]);

        // Add players
        BattlePlayer::create([
            'battle_id' => $battle->id,
            'user_id' => $player1->id,
            'team_id' => $team1->id,
            'player_slot' => 'p1',
            'is_ai' => false,
        ]);

        BattlePlayer::create([
            'battle_id' => $battle->id,
            'user_id' => $player2->id,
            'team_id' => $team2->id,
            'player_slot' => 'p2',
            'is_ai' => false,
        ]);

        return $battle;
    }

    /**
     * Find queued players waiting for a battle.
     */
    public function findQueuedPlayers(int $limit = 10): Collection
    {
        // TODO: Implement a queue system
        // For now, just return empty collection
        return collect();
    }

    /**
     * Match two players from the queue.
     */
    public function matchFromQueue(): ?Battle
    {
        $queue = $this->findQueuedPlayers(2);

        if ($queue->count() < 2) {
            return null;
        }

        $player1 = $queue->shift();
        $player2 = $queue->shift();

        $team1 = $player1->teams()->first();
        $team2 = $player2->teams()->first();

        if (!$team1 || !$team2) {
            return null;
        }

        return $this->createPvpBattle($player1, $team1, $player2, $team2);
    }

    /**
     * Calculate battle difficulty (for future ELO/rating system).
     */
    public function calculateMatchDifficulty(User $player1, User $player2): float
    {
        // TODO: Implement difficulty calculation based on ratings
        return 0.5; // 50% chance for each player
    }
}
