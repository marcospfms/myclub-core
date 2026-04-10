<?php

namespace App\Jobs;

use Illuminate\Foundation\Queue\Queueable;
use App\Enums\MatchConfirmation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\FriendlyMatch;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExpireFriendlyMatchInvitations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        FriendlyMatch::query()
            ->where('confirmation', MatchConfirmation::Pending)
            ->where('invite_expires_at', '<=', now())
            ->update(['confirmation' => MatchConfirmation::Expired]);
    }
}
