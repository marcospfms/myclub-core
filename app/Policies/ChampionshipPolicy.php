<?php

namespace App\Policies;

use App\Models\Championship;
use App\Models\User;

class ChampionshipPolicy
{
    public function view(User $user, Championship $championship): bool
    {
        if ($user->isAdmin() || $user->id === $championship->created_by) {
            return true;
        }

        return $championship->teams()
            ->whereHas('teamSportMode.team', fn ($query) => $query->where('owner_id', $user->id))
            ->exists();
    }

    public function update(User $user, Championship $championship): bool
    {
        return $user->id === $championship->created_by || $user->isAdmin();
    }

    public function delete(User $user, Championship $championship): bool
    {
        return ($user->id === $championship->created_by || $user->isAdmin())
            && $championship->isDraft();
    }

    public function manageLifecycle(User $user, Championship $championship): bool
    {
        return $user->id === $championship->created_by || $user->isAdmin();
    }

    public function cancelActive(User $user, Championship $championship): bool
    {
        return $user->isAdmin();
    }

    public function manageEnrollment(User $user, Championship $championship): bool
    {
        return $user->id === $championship->created_by || $user->isAdmin();
    }

    public function enroll(User $user, Championship $championship): bool
    {
        return true;
    }

    public function manageMatch(User $user, Championship $championship): bool
    {
        return $user->id === $championship->created_by || $user->isAdmin();
    }
}
