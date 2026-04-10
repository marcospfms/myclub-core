<?php

namespace Tests\Feature\Phase2;

use App\Enums\MatchConfirmation;
use App\Jobs\ExpireFriendlyMatchInvitations;
use App\Models\FriendlyMatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpireFriendlyMatchInvitationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_invites_are_marked_as_expired(): void
    {
        $expiredMatch = FriendlyMatch::factory()->pending()->create([
            'invite_expires_at' => now()->subDay(),
        ]);

        $activeMatch = FriendlyMatch::factory()->pending()->create([
            'invite_expires_at' => now()->addDay(),
        ]);

        $confirmedMatch = FriendlyMatch::factory()->scheduled()->create([
            'invite_expires_at' => now()->subDay(),
        ]);

        (new ExpireFriendlyMatchInvitations)->handle();

        $this->assertSame(
            MatchConfirmation::Expired->value,
            $expiredMatch->fresh()->confirmation->value,
        );

        $this->assertSame(
            MatchConfirmation::Pending->value,
            $activeMatch->fresh()->confirmation->value,
        );

        $this->assertSame(
            MatchConfirmation::Confirmed->value,
            $confirmedMatch->fresh()->confirmation->value,
        );
    }
}
