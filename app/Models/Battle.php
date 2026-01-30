<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Battle extends Model
{
    /** @use HasFactory<\Database\Factories\BattleFactory> */
    use HasFactory;

    protected $fillable = [
        'type',
        'format',
        'status',
        'showdown_id',
        'showdown_room_id',
        'winner_id',
        'replay_log',
    ];

    protected $casts = [
        'replay_log' => 'array',
    ];

    /**
     * A battle has many players.
     */
    public function players(): HasMany
    {
        return $this->hasMany(BattlePlayer::class);
    }

    /**
     * A battle has many turn decisions.
     */
    public function turnDecisions(): HasMany
    {
        return $this->hasMany(TurnDecision::class);
    }

    /**
     * Get the winner of this battle.
     */
    public function winner(): ?User
    {
        return $this->winner_id ? User::find($this->winner_id) : null;
    }

    /**
     * Get player by slot.
     */
    public function getPlayerBySlot(string $slot): ?BattlePlayer
    {
        return $this->players()->where('player_slot', $slot)->first();
    }

    /**
     * Get all users involved in this battle.
     */
    public function getUsers(): array
    {
        return $this->players()
            ->whereNotNull('user_id')
            ->with('user')
            ->get()
            ->map(fn($p) => $p->user)
            ->toArray();
    }

    /**
     * Check if battle is finished.
     */
    public function isFinished(): bool
    {
        return $this->status === 'finished';
    }

    /**
     * Finish the battle with a winner.
     */
    public function finish(?string $winnerId = null, array $replayLog = []): void
    {
        $this->status = 'finished';
        $this->winner_id = $winnerId;
        $this->replay_log = $replayLog;
        $this->save();
    }
}
