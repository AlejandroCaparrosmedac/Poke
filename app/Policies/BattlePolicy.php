<?php

namespace App\Policies;

use App\Models\Battle;
use App\Models\User;

class BattlePolicy
{
    /**
     * Determine if user can view the battle.
     */
    public function view(User $user, Battle $battle): bool
    {
        // User can view if they're part of the battle
        return $battle->players()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine if user can participate in the battle.
     */
    public function participate(User $user, Battle $battle): bool
    {
        // User can participate if they're part of the battle and it's active
        return $battle->status === 'active' && $this->view($user, $battle);
    }
}
