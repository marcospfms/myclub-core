<?php

namespace App\Services\Team;

use App\Enums\InvitationStatus;
use App\Models\User;
use App\Models\TeamInvitation;
use App\Models\TeamSportMode;
use DomainException;

class TeamInvitationService
{
    public function send(TeamSportMode $teamSportMode, array $data, User $sender): TeamInvitation
    {
        if ($teamSportMode->activeMemberships()->where('player_id', $data['invited_user_id'])->exists()) {
            throw new DomainException('Usuário já é membro ativo desta equipe.');
        }

        $teamSportMode->invitations()
            ->where('invited_user_id', $data['invited_user_id'])
            ->where('status', InvitationStatus::Pending)
            ->update(['status' => InvitationStatus::Expired]);

        return TeamInvitation::create(array_merge($data, [
            'team_sport_mode_id' => $teamSportMode->id,
            'invited_by' => $sender->id,
            'status' => InvitationStatus::Pending,
            'expires_at' => now()->addDays(7),
        ]));
    }

    public function reject(TeamInvitation $invitation): void
    {
        $invitation->update(['status' => InvitationStatus::Rejected]);
    }
}
