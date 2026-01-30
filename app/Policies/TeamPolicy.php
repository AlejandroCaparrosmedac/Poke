<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    /**
     * Determine if user can view the team.
     */
    public function view(User $user, Team $team): bool
    {
        return $user->id === $team->user_id;
    }

    /**
     * Determine if user can update the team.
     */
    public function update(User $user, Team $team): bool
    {
        return $user->id === $team->user_id;
    }

    /**
     * Determine if user can delete the team.
     */
    public function delete(User $user, Team $team): bool
    {
        return $user->id === $team->user_id;
    }
}
