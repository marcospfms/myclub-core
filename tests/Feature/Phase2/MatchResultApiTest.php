<?php

namespace Tests\Feature\Phase2;

use App\Enums\MatchStatus;
use App\Enums\ResultStatus;
use App\Models\FriendlyMatch;
use App\Models\SportMode;
use App\Models\Team;
use App\Models\TeamSportMode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MatchResultApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_owner_can_register_result(): void
    {
        [$homeOwner, $homeTeamSportMode] = $this->createOwnedTeamSportMode();
        [, $awayTeamSportMode] = $this->createOwnedTeamSportMode($homeTeamSportMode->sport_mode_id);

        $match = FriendlyMatch::factory()->scheduled()->create([
            'home_team_id' => $homeTeamSportMode->id,
            'away_team_id' => $awayTeamSportMode->id,
        ]);

        Sanctum::actingAs($homeOwner);

        $this->postJson(route('api.v1.friendly-matches.result.store', $match), [
            'home_goals' => 2,
            'away_goals' => 1,
            'home_notes' => 'Placar justo',
            'away_notes' => 'Nao deve entrar para o mandante',
        ])->assertOk()
            ->assertJsonPath('data.result_status', ResultStatus::Pending->value)
            ->assertJsonPath('data.home_notes', 'Placar justo')
            ->assertJsonMissingPath('data.away_notes');

        $this->assertDatabaseHas('friendly_matches', [
            'id' => $match->id,
            'home_goals' => 2,
            'away_goals' => 1,
            'result_status' => ResultStatus::Pending->value,
            'result_registered_by' => $homeOwner->id,
            'home_notes' => 'Placar justo',
        ]);
    }

    public function test_other_owner_can_confirm_result(): void
    {
        [$homeOwner, $homeTeamSportMode] = $this->createOwnedTeamSportMode();
        [$awayOwner, $awayTeamSportMode] = $this->createOwnedTeamSportMode($homeTeamSportMode->sport_mode_id);

        $match = FriendlyMatch::factory()->withPendingResult($homeOwner)->create([
            'home_team_id' => $homeTeamSportMode->id,
            'away_team_id' => $awayTeamSportMode->id,
        ]);

        Sanctum::actingAs($awayOwner);

        $this->postJson(route('api.v1.friendly-matches.result.confirm', $match))
            ->assertOk()
            ->assertJsonPath('data.result_status', ResultStatus::Confirmed->value)
            ->assertJsonPath('data.match_status', MatchStatus::Completed->value);
    }

    public function test_registrar_cannot_confirm_own_result(): void
    {
        [$homeOwner, $homeTeamSportMode] = $this->createOwnedTeamSportMode();
        [, $awayTeamSportMode] = $this->createOwnedTeamSportMode($homeTeamSportMode->sport_mode_id);

        $match = FriendlyMatch::factory()->withPendingResult($homeOwner)->create([
            'home_team_id' => $homeTeamSportMode->id,
            'away_team_id' => $awayTeamSportMode->id,
        ]);

        Sanctum::actingAs($homeOwner);

        $this->postJson(route('api.v1.friendly-matches.result.confirm', $match))
            ->assertForbidden();
    }

    public function test_other_owner_can_dispute_result(): void
    {
        [$homeOwner, $homeTeamSportMode] = $this->createOwnedTeamSportMode();
        [$awayOwner, $awayTeamSportMode] = $this->createOwnedTeamSportMode($homeTeamSportMode->sport_mode_id);

        $match = FriendlyMatch::factory()->withPendingResult($homeOwner)->create([
            'home_team_id' => $homeTeamSportMode->id,
            'away_team_id' => $awayTeamSportMode->id,
        ]);

        Sanctum::actingAs($awayOwner);

        $this->postJson(route('api.v1.friendly-matches.result.dispute', $match))
            ->assertOk()
            ->assertJsonPath('data.result_status', ResultStatus::Disputed->value)
            ->assertJsonPath('data.home_goals', null)
            ->assertJsonPath('data.away_goals', null);
    }

    public function test_cannot_register_result_when_pending_confirmation_or_already_pending_result(): void
    {
        [$homeOwner, $homeTeamSportMode] = $this->createOwnedTeamSportMode();
        [, $awayTeamSportMode] = $this->createOwnedTeamSportMode($homeTeamSportMode->sport_mode_id);

        $pendingInviteMatch = FriendlyMatch::factory()->pending()->create([
            'home_team_id' => $homeTeamSportMode->id,
            'away_team_id' => $awayTeamSportMode->id,
        ]);

        $pendingResultMatch = FriendlyMatch::factory()->withPendingResult($homeOwner)->create([
            'home_team_id' => $homeTeamSportMode->id,
            'away_team_id' => $awayTeamSportMode->id,
        ]);

        Sanctum::actingAs($homeOwner);

        $this->postJson(route('api.v1.friendly-matches.result.store', $pendingInviteMatch), [
            'home_goals' => 1,
            'away_goals' => 0,
        ])->assertStatus(409);

        $this->postJson(route('api.v1.friendly-matches.result.store', $pendingResultMatch), [
            'home_goals' => 3,
            'away_goals' => 2,
        ])->assertStatus(409);
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
