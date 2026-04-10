<?php

namespace Tests\Feature\Phase3;

use App\Enums\ChampionshipStatus;
use App\Jobs\ArchiveFinishedChampionships;
use App\Models\Championship;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchiveFinishedChampionshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_finished_championships_older_than_seven_days_are_archived(): void
    {
        $old = Championship::factory()->finished()->create([
            'updated_at' => now()->subDays(8),
        ]);

        $fresh = Championship::factory()->finished()->create([
            'updated_at' => now()->subDays(2),
        ]);

        (new ArchiveFinishedChampionships)->handle();

        $this->assertSame(ChampionshipStatus::Archived, $old->fresh()->status);
        $this->assertSame(ChampionshipStatus::Finished, $fresh->fresh()->status);
    }
}
