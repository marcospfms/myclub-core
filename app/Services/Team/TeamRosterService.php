<?php

namespace App\Services\Team;

use App\Enums\InvitationStatus;
use App\Models\Player;
use App\Models\TeamInvitation;
use App\Models\PlayerMembership;
use App\Models\TeamSportMode;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TeamRosterService
{
    public function listActiveMembers(TeamSportMode $teamSportMode): Collection
    {
        return $teamSportMode->activeMemberships()
            ->with(['player.user', 'position'])
            ->get();
    }

    public function removeMember(PlayerMembership $membership): void
    {
        $membership->update(['left_at' => now()]);
    }

    public function leaveTeam(PlayerMembership $membership): void
    {
        $membership->update(['left_at' => now()]);
    }

    public function acceptInvitation(TeamInvitation $invitation): PlayerMembership
    {
        return DB::transaction(function () use ($invitation): PlayerMembership {
            if (PlayerMembership::query()
                ->where('team_sport_mode_id', $invitation->team_sport_mode_id)
                ->where('player_id', $invitation->invited_user_id)
                ->whereNull('left_at')
                ->exists()) {
                throw new DomainException('Usuário já é membro ativo desta equipe.');
            }

            $invitation->update(['status' => InvitationStatus::Accepted]);

            Player::firstOrCreate(['user_id' => $invitation->invited_user_id]);

            return PlayerMembership::create([
                'team_sport_mode_id' => $invitation->team_sport_mode_id,
                'player_id' => $invitation->invited_user_id,
                'position_id' => $invitation->position_id,
                'is_starter' => false,
            ]);
        });
    }
}
