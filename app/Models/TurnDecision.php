<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TurnDecision extends Model
{
    /** @use HasFactory<\Database\Factories\TurnDecisionFactory> */
    use HasFactory;

    protected $fillable = [
        'battle_id',
        'battle_player_id',
        'turn_number',
        'decision_type',
        'decision_data',
        'decision_result',
        'status',
    ];

    protected $casts = [
        'decision_data' => 'array',
        'decision_result' => 'array',
    ];

    /**
     * A turn decision belongs to a battle.
     */
    public function battle(): BelongsTo
    {
        return $this->belongsTo(Battle::class);
    }

    /**
     * A turn decision belongs to a battle player.
     */
    public function battlePlayer(): BelongsTo
    {
        return $this->belongsTo(BattlePlayer::class);
    }

    /**
     * Mark this decision as executed.
     */
    public function markExecuted(array $result = []): void
    {
        $this->status = 'executed';
        $this->decision_result = $result;
        $this->save();
    }

    /**
     * Mark this decision as failed.
     */
    public function markFailed(): void
    {
        $this->status = 'failed';
        $this->save();
    }

    /**
     * Check if decision is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
