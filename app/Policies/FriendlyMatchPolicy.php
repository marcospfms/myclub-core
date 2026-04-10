<?php

namespace App\Policies;

use App\Models\FriendlyMatch;
use App\Models\User;

class FriendlyMatchPolicy
{
    public function view(?User $user, FriendlyMatch $match): bool
    {
        if ($match->is_public) {
            return true;
        }

        if (! $user instanceof User) {
            return false;
        }

        return $user->isAdmin() || $this->isParticipantOwner($user, $match);
    }

    public function delete(User $user, FriendlyMatch $match): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $match->isPending() && $user->id === $match->homeTeam->team->owner_id;
    }

    public function respond(User $user, FriendlyMatch $match): bool
    {
        return $user->isAdmin() || $user->id === $match->awayTeam->team->owner_id;
    }

    public function manage(User $user, FriendlyMatch $match): bool
    {
        return $user->isAdmin() || $this->isParticipantOwner($user, $match);
    }

    public function manageResult(User $user, FriendlyMatch $match): bool
    {
        return $this->manage($user, $match);
    }

    public function manageHighlights(User $user, FriendlyMatch $match): bool
    {
        return $this->manage($user, $match);
    }

    private function isParticipantOwner(User $user, FriendlyMatch $match): bool
    {
        return in_array($user->id, [
            $match->homeTeam->team->owner_id,
            $match->awayTeam->team->owner_id,
        ], true);
    }
}
