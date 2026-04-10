<?php

namespace Tests\Feature\Phase3;

use App\Enums\ChampionshipStatus;
use App\Models\BadgeType;
use App\Models\Championship;
use App\Models\ChampionshipMatch;
use App\Models\PlayerMembership;
use App\Models\SportMode;
use App\Models\Team;
use App\Models\TeamSportMode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ChampionshipMatchApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_creator_can_register_match_result(): void
    {
        $user = User::factory()->create();
        $championship = Championship::factory()->active()->for($user, 'creator')->create();
        $match = ChampionshipMatch::factory()->scheduled()->forChampionship($championship)->create();

        Sanctum::actingAs($user);

        $this->putJson(route('api.v1.championships.matches.update', [$championship, $match]), [
            'home_goals' => 2,
            'away_goals' => 1,
        ])->assertOk()
            ->assertJsonPath('data.match_status', 'completed');
    }

    public function test_non_creator_cannot_register_match_result(): void
    {
        $otherUser = User::factory()->create();
        $championship = Championship::factory()->active()->create();
        $match = ChampionshipMatch::factory()->scheduled()->forChampionship($championship)->create();

        Sanctum::actingAs($otherUser);

        $this->putJson(route('api.v1.championships.matches.update', [$championship, $match]), [
            'home_goals' => 1,
            'away_goals' => 0,
        ])->assertForbidden();
    }

    public function test_registering_last_match_can_finish_championship(): void
    {
        BadgeType::factory()->create(['name' => 'top_scorer']);
        BadgeType::factory()->create(['name' => 'best_assist']);
        BadgeType::factory()->create(['name' => 'golden_ball']);
        BadgeType::factory()->create(['name' => 'fair_play']);
        BadgeType::factory()->create(['name' => 'hat_trick']);

        $creator = User::factory()->create();
        $sportMode = SportMode::factory()->create();
        $championship = Championship::factory()->active()->for($creator, 'creator')->create();

        $homeTeam = $this->createOwnedTeamSportMode(User::factory()->create(), $sportMode);
        $awayTeam = $this->createOwnedTeamSportMode(User::factory()->create(), $sportMode);

        $championship->sportModes()->sync([$sportMode->id]);
        $championship->teams()->create(['team_sport_mode_id' => $homeTeam->id]);
        $championship->teams()->create(['team_sport_mode_id' => $awayTeam->id]);

        $homeMembership = PlayerMembership::factory()->create(['team_sport_mode_id' => $homeTeam->id]);
        $awayMembership = PlayerMembership::factory()->create(['team_sport_mode_id' => $awayTeam->id]);

        $championship->phases()->firstOrCreate([
            'name' => 'Fase Principal',
            'type' => 'group_stage',
            'phase_order' => 1,
            'legs' => 1,
            'advances_count' => 0,
        ]);
        $phase = $championship->phases()->first();
        $round = $phase->rounds()->firstOrCreate([
            'name' => 'Rodada 1',
            'round_number' => 1,
        ]);

        $match = ChampionshipMatch::factory()->scheduled()->create([
            'championship_round_id' => $round->id,
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
        ]);

        $championship->teams()->where('team_sport_mode_id', $homeTeam->id)->first()->players()->create([
            'championship_id' => $championship->id,
            'team_sport_mode_id' => $homeTeam->id,
            'player_membership_id' => $homeMembership->id,
        ]);
        $championship->teams()->where('team_sport_mode_id', $awayTeam->id)->first()->players()->create([
            'championship_id' => $championship->id,
            'team_sport_mode_id' => $awayTeam->id,
            'player_membership_id' => $awayMembership->id,
        ]);

        Sanctum::actingAs($creator);

        $this->putJson(route('api.v1.championships.matches.update', [$championship, $match]), [
            'home_goals' => 3,
            'away_goals' => 1,
        ])->assertOk();

        $this->postJson(route('api.v1.championships.matches.highlights.store', [$championship, $match]), [
            'highlights' => [
                [
                    'player_membership_id' => $homeMembership->id,
                    'goals' => 3,
                    'assists' => 1,
                    'is_mvp' => true,
                ],
                [
                    'player_membership_id' => $awayMembership->id,
                    'goals' => 1,
                ],
            ],
        ])->assertOk();

        $match->refresh();
        $championship->refresh();

        $this->assertSame('completed', $match->match_status->value);
        $this->assertSame(ChampionshipStatus::Finished, $championship->status);
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
