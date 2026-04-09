<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    public function update(User $user, Team $team): bool
    {
        return $user->id === $team->owner_id || $user->isAdmin();
    }

    public function delete(User $user, Team $team): bool
    {
        return $user->id === $team->owner_id || $user->isAdmin();
    }

    public function manageRoster(User $user, Team $team): bool
    {
        return $user->id === $team->owner_id || $user->isAdmin();
    }
}
