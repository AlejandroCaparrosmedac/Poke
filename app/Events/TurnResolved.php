<?php

namespace App\Events;

use App\Models\Battle;
use App\Models\BattlePlayer;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TurnResolved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Battle $battle,
        public BattlePlayer $battlePlayer
    ) {
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $players = $this->battle->players()->pluck('user_id')->filter()->toArray();

        return array_map(fn($userId) => new PrivateChannel("user.{$userId}"), $players);
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'battleId' => $this->battle->id,
            'playerSlot' => $this->battlePlayer->player_slot,
            'playerName' => $this->battlePlayer->user->name,
            'turnNumber' => $this->battlePlayer->current_turn,
            'lastDecision' => $this->battlePlayer->getLastDecision(),
        ];
    }
}
