<?php

namespace Tests\Feature\Phase3;

use App\Models\Championship;
use App\Models\PlayerMembership;
use App\Models\SportMode;
use App\Models\Team;
use App\Models\TeamSportMode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ChampionshipEnrollmentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_owner_can_enroll_own_team(): void
    {
        $owner = User::factory()->create();
        $sportMode = SportMode::factory()->create();
        $teamSportMode = $this->createOwnedTeamSportMode($owner, $sportMode);
        $championship = Championship::factory()->enrollment()->withSportMode($sportMode)->create();

        Sanctum::actingAs($owner);

        $this->postJson(route('api.v1.championships.teams.enroll', $championship), [
            'team_sport_mode_id' => $teamSportMode->id,
        ])->assertCreated();

        $this->assertDatabaseHas('championship_teams', [
            'championship_id' => $championship->id,
            'team_sport_mode_id' => $teamSportMode->id,
        ]);
    }

    public function test_cannot_enroll_team_with_wrong_sport_mode(): void
    {
        $owner = User::factory()->create();
        $teamSportMode = TeamSportMode::factory()->create();
        $teamSportMode->team->update(['owner_id' => $owner->id]);
        $championship = Championship::factory()->enrollment()->withSportMode()->create();

        Sanctum::actingAs($owner);

        $this->postJson(route('api.v1.championships.teams.enroll', $championship), [
            'team_sport_mode_id' => $teamSportMode->id,
        ])->assertStatus(409);
    }

    public function test_owner_can_select_players_for_championship(): void
    {
        $owner = User::factory()->create();
        $sportMode = SportMode::factory()->create();
        $teamSportMode = $this->createOwnedTeamSportMode($owner, $sportMode);
        $memberships = PlayerMembership::factory()->count(3)->create([
            'team_sport_mode_id' => $teamSportMode->id,
        ]);
        $championship = Championship::factory()->enrollment()->withSportMode($sportMode)->create();
        $championship->teams()->create(['team_sport_mode_id' => $teamSportMode->id]);

        Sanctum::actingAs($owner);

        $this->postJson(route('api.v1.championships.teams.players.store', [$championship, $teamSportMode]), [
            'player_membership_ids' => $memberships->pluck('id')->all(),
        ])->assertOk()
            ->assertJsonCount(3, 'data');

        $this->assertDatabaseCount('championship_team_players', 3);
    }

    private function createOwnedTeamSportMode(User $owner, SportMode $sportMode): TeamSportMode
    {
        $team = Team::factory()->create(['owner_id' => $owner->id]);

        return TeamSportMode::factory()->create([
            'team_id' => $team->id,
            'sport_mode_id' => $sportMode->id,
        ]);
    }
}
