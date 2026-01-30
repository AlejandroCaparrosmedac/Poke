<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BattlePlayer extends Model
{
    /** @use HasFactory<\Database\Factories\BattlePlayerFactory> */
    use HasFactory;

    protected $fillable = [
        'battle_id',
        'user_id',
        'team_id',
        'player_slot',
        'is_ai',
        'is_winner',
        'current_turn',
    ];

    protected $casts = [
        'is_ai' => 'boolean',
        'is_winner' => 'boolean',
    ];

    /**
     * A battle player belongs to a battle.
     */
    public function battle(): BelongsTo
    {
        return $this->belongsTo(Battle::class);
    }

    /**
     * A battle player belongs to a user (nullable for AI).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault(['name' => 'AI Opponent']);
    }

    /**
     * A battle player has a team.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * A battle player has many turn decisions.
     */
    public function turnDecisions(): HasMany
    {
        return $this->hasMany(TurnDecision::class);
    }

    /**
     * Get the opponent in this battle.
     */
    public function getOpponent(): ?BattlePlayer
    {
        $oppositeSlot = $this->player_slot === 'p1' ? 'p2' : 'p1';
        return $this->battle->getPlayerBySlot($oppositeSlot);
    }

    /**
     * Get the last decision made by this player.
     */
    public function getLastDecision(): ?TurnDecision
    {
        return $this->turnDecisions()
            ->orderBy('turn_number', 'desc')
            ->first();
    }

    /**
     * Record a decision for the current turn.
     */
    public function recordDecision(int $turnNumber, string $decisionType, array $decisionData): TurnDecision
    {
        return $this->turnDecisions()->create([
            'battle_id' => $this->battle_id,
            'turn_number' => $turnNumber,
            'decision_type' => $decisionType,
            'decision_data' => $decisionData,
            'status' => 'pending',
        ]);
    }
}
