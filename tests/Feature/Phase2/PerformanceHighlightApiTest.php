<?php

namespace Tests\Feature\Phase2;

use App\Models\FriendlyMatch;
use App\Models\PerformanceHighlight;
use App\Models\Player;
use App\Models\PlayerMembership;
use App\Models\Position;
use App\Models\SportMode;
use App\Models\Team;
use App\Models\TeamSportMode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PerformanceHighlightApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_owner_can_register_highlights_for_own_players(): void
    {
        [$homeOwner, $homeTeamSportMode] = $this->createOwnedTeamSportMode();
        [, $awayTeamSportMode] = $this->createOwnedTeamSportMode($homeTeamSportMode->sport_mode_id);

        $membership = $this->createMembershipForTeamSportMode($homeTeamSportMode);

        $match = FriendlyMatch::factory()->completed()->create([
            'home_team_id' => $homeTeamSportMode->id,
            'away_team_id' => $awayTeamSportMode->id,
        ]);

        Sanctum::actingAs($homeOwner);

        $this->postJson(route('api.v1.friendly-matches.highlights.store', $match), [
            'highlights' => [[
                'player_membership_id' => $membership->id,
                'goals' => 2,
                'assists' => 1,
                'yellow_cards' => 0,
                'red_cards' => 0,
            ]],
        ])->assertOk()
            ->assertJsonPath('data.0.player_membership.id', $membership->id)
            ->assertJsonPath('data.0.goals', 2);

        $this->assertDatabaseHas('performance_highlights', [
            'friendly_match_id' => $match->id,
            'player_membership_id' => $membership->id,
            'goals' => 2,
        ]);
    }

    public function test_owner_cannot_register_highlights_for_opponent_players(): void
    {
        [$homeOwner, $homeTeamSportMode] = $this->createOwnedTeamSportMode();
        [, $awayTeamSportMode] = $this->createOwnedTeamSportMode($homeTeamSportMode->sport_mode_id);

        $awayMembership = $this->createMembershipForTeamSportMode($awayTeamSportMode);

        $match = FriendlyMatch::factory()->completed()->create([
            'home_team_id' => $homeTeamSportMode->id,
            'away_team_id' => $awayTeamSportMode->id,
        ]);

        Sanctum::actingAs($homeOwner);

        $this->postJson(route('api.v1.friendly-matches.highlights.store', $match), [
            'highlights' => [[
                'player_membership_id' => $awayMembership->id,
                'goals' => 1,
            ]],
        ])->assertForbidden();
    }

    public function test_cannot_register_highlights_before_match_is_completed(): void
    {
        [$homeOwner, $homeTeamSportMode] = $this->createOwnedTeamSportMode();
        [, $awayTeamSportMode] = $this->createOwnedTeamSportMode($homeTeamSportMode->sport_mode_id);

        $membership = $this->createMembershipForTeamSportMode($homeTeamSportMode);

        $match = FriendlyMatch::factory()->scheduled()->create([
            'home_team_id' => $homeTeamSportMode->id,
            'away_team_id' => $awayTeamSportMode->id,
        ]);

        Sanctum::actingAs($homeOwner);

        $this->postJson(route('api.v1.friendly-matches.highlights.store', $match), [
            'highlights' => [[
                'player_membership_id' => $membership->id,
                'goals' => 1,
            ]],
        ])->assertStatus(409);
    }

    public function test_public_match_highlights_can_be_listed_without_authentication(): void
    {
        $match = FriendlyMatch::factory()->completed()->create(['is_public' => true]);
        $membership = $this->createMembershipForTeamSportMode(TeamSportMode::query()->findOrFail($match->home_team_id));

        $highlight = PerformanceHighlight::factory()->create([
            'friendly_match_id' => $match->id,
            'player_membership_id' => $membership->id,
            'goals' => 1,
        ]);

        $this->getJson(route('api.v1.friendly-matches.highlights.index', $match))
            ->assertOk()
            ->assertJsonPath('data.0.id', $highlight->id)
            ->assertJsonPath('data.0.goals', 1);
    }

    private function createMembershipForTeamSportMode(TeamSportMode $teamSportMode): PlayerMembership
    {
        $player = Player::factory()->create();

        return PlayerMembership::factory()->create([
            'team_sport_mode_id' => $teamSportMode->id,
            'player_id' => $player->user_id,
            'position_id' => Position::query()->firstOrFail()->id,
        ]);
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
