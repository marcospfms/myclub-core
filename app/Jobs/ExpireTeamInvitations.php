<?php

namespace App\Jobs;

use App\Enums\InvitationStatus;
use App\Models\TeamInvitation;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExpireTeamInvitations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        TeamInvitation::query()
            ->where('status', InvitationStatus::Pending)
            ->where('expires_at', '<', now())
            ->update(['status' => InvitationStatus::Expired]);
    }
}
