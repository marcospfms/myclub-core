<?php

namespace Tests\Feature\Phase2;

use App\Enums\MatchConfirmation;
use App\Enums\MatchStatus;
use App\Models\FriendlyMatch;
use App\Models\SportMode;
use App\Models\Team;
use App\Models\TeamSportMode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FriendlyMatchApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_authenticated_owner_can_create_and_list_owned_friendly_matches(): void
    {
        [$homeOwner, $homeTeamSportMode] = $this->createOwnedTeamSportMode();
        [, $awayTeamSportMode] = $this->createOwnedTeamSportMode($homeTeamSportMode->sport_mode_id);

        Sanctum::actingAs($homeOwner);

        $storeResponse = $this->postJson(route('api.v1.friendly-matches.store'), [
            'home_team_id' => $homeTeamSportMode->id,
            'away_team_id' => $awayTeamSportMode->id,
            'scheduled_at' => now()->addWeek()->toDateTimeString(),
            'location' => 'Arena Norte',
            'is_public' => true,
        ]);

        $matchId = $storeResponse->json('data.id');

        $storeResponse
            ->assertCreated()
            ->assertJsonPath('data.id', $matchId)
            ->assertJsonPath('data.confirmation', MatchConfirmation::Pending->value)
            ->assertJsonPath('data.is_public', true);

        $this->getJson(route('api.v1.friendly-matches.index'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matchId);
    }

    public function test_cannot_create_friendly_match_with_different_sport_modes(): void
    {
        [$homeOwner, $homeTeamSportMode] = $this->createOwnedTeamSportMode();
        $differentSportModeId = SportMode::query()
            ->whereKeyNot($homeTeamSportMode->sport_mode_id)
            ->value('id');

        [, $awayTeamSportMode] = $this->createOwnedTeamSportMode($differentSportModeId);

        Sanctum::actingAs($homeOwner);

        $this->postJson(route('api.v1.friendly-matches.store'), [
            'home_team_id' => $homeTeamSportMode->id,
            'away_team_id' => $awayTeamSportMode->id,
            'scheduled_at' => now()->addWeek()->toDateTimeString(),
        ])->assertStatus(409)
            ->assertJsonPath('success', false);
    }

    public function test_public_match_can_be_viewed_without_authentication_and_private_match_cannot(): void
    {
        $publicMatch = FriendlyMatch::factory()->pending()->create(['is_public' => true]);
        $privateMatch = FriendlyMatch::factory()->pending()->create(['is_public' => false]);

        $this->getJson(route('api.v1.friendly-matches.show', $publicMatch))
            ->assertOk()
            ->assertJsonPath('data.id', $publicMatch->id);

        $this->getJson(route('api.v1.friendly-matches.show', $privateMatch))
            ->assertForbidden();
    }

    public function test_away_owner_can_confirm_match_but_home_owner_cannot_confirm_own_invite(): void
    {
        [$homeOwner, $homeTeamSportMode] = $this->createOwnedTeamSportMode();
        [$awayOwner, $awayTeamSportMode] = $this->createOwnedTeamSportMode($homeTeamSportMode->sport_mode_id);

        $match = FriendlyMatch::factory()->pending()->create([
            'home_team_id' => $homeTeamSportMode->id,
            'away_team_id' => $awayTeamSportMode->id,
        ]);

        Sanctum::actingAs($homeOwner);

        $this->postJson(route('api.v1.friendly-matches.confirm', $match))
            ->assertForbidden();

        Sanctum::actingAs($awayOwner);

        $this->postJson(route('api.v1.friendly-matches.confirm', $match))
            ->assertOk()
            ->assertJsonPath('data.confirmation', MatchConfirmation::Confirmed->value)
            ->assertJsonPath('data.match_status', MatchStatus::Scheduled->value);
    }

    public function test_home_owner_can_remove_pending_invite(): void
    {
        [$homeOwner, $homeTeamSportMode] = $this->createOwnedTeamSportMode();
        [, $awayTeamSportMode] = $this->createOwnedTeamSportMode($homeTeamSportMode->sport_mode_id);

        $match = FriendlyMatch::factory()->pending()->create([
            'home_team_id' => $homeTeamSportMode->id,
            'away_team_id' => $awayTeamSportMode->id,
        ]);

        Sanctum::actingAs($homeOwner);

        $this->deleteJson(route('api.v1.friendly-matches.destroy', $match))
            ->assertOk()
            ->assertJsonPath('message', 'Convite removido.');

        $this->assertDatabaseMissing('friendly_matches', [
            'id' => $match->id,
        ]);
    }

    public function test_either_owner_can_cancel_confirmed_match(): void
    {
        [$homeOwner, $homeTeamSportMode] = $this->createOwnedTeamSportMode();
        [$awayOwner, $awayTeamSportMode] = $this->createOwnedTeamSportMode($homeTeamSportMode->sport_mode_id);

        $match = FriendlyMatch::factory()->scheduled()->create([
            'home_team_id' => $homeTeamSportMode->id,
            'away_team_id' => $awayTeamSportMode->id,
        ]);

        Sanctum::actingAs($awayOwner);

        $this->postJson(route('api.v1.friendly-matches.cancel', $match))
            ->assertOk()
            ->assertJsonPath('data.match_status', MatchStatus::Cancelled->value);

        Sanctum::actingAs($homeOwner);

        $this->getJson(route('api.v1.friendly-matches.show', $match))
            ->assertOk()
            ->assertJsonPath('data.id', $match->id);
    }

    public function test_friendly_match_management_routes_require_authentication(): void
    {
        $this->getJson(route('api.v1.friendly-matches.index'))
            ->assertUnauthorized();
    }

    /**
     * @return array{0: User, 1: TeamSportMode}
     */
    private function createOwnedTeamSportMode(?int $sportModeId = null): array
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $owner->id]);
        $sportMode = $sportModeId !== null
            ? SportMode::query()->findOrFail($sportModeId)
            : SportMode::query()->firstOrFail();

        $teamSportMode = TeamSportMode::factory()->create([
            'team_id' => $team->id,
            'sport_mode_id' => $sportMode->id,
        ]);

        return [$owner, $teamSportMode];
    }
}
