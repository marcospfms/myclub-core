<?php

namespace App\Jobs;

use App\Enums\ChampionshipStatus;
use App\Models\Championship;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ArchiveFinishedChampionships implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Championship::query()
            ->where('status', ChampionshipStatus::Finished->value)
            ->where('updated_at', '<=', now()->subDays(7))
            ->update(['status' => ChampionshipStatus::Archived->value]);
    }
}
