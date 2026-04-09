<?php

namespace App\Services\Team;

use App\Enums\InvitationStatus;
use App\Models\Player;
use App\Models\TeamInvitation;
use App\Models\PlayerMembership;
use Illuminate\Support\Facades\DB;

class TeamRosterService
{
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
