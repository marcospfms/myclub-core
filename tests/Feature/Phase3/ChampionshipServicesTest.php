<?php

namespace Tests\Feature\Phase3;

use App\Enums\BadgeScope;
use App\Enums\ChampionshipFormat;
use App\Enums\ChampionshipStatus;
use App\Enums\MatchStatus;
use App\Models\BadgeType;
use App\Models\Category;
use App\Models\Championship;
use App\Models\ChampionshipAward;
use App\Models\PlayerBadge;
use App\Models\PlayerMembership;
use App\Models\SportMode;
use App\Models\Team;
use App\Models\TeamSportMode;
use App\Models\User;
use App\Services\Championship\ChampionshipClosingService;
use App\Services\Championship\ChampionshipEnrollmentService;
use App\Services\Championship\ChampionshipMatchService;
use App\Services\Championship\ChampionshipService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChampionshipServicesTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_user_cannot_create_more_than_one_active_league(): void
    {
        $creator = User::factory()->create();
        $category = Category::factory()->create();
        $sportMode = SportMode::factory()->create();
        $service = new ChampionshipService;

        $first = $service->create([
            'name' => 'Liga 1',
            'format' => ChampionshipFormat::League,
            'category_id' => $category->id,
            'sport_mode_ids' => [$sportMode->id],
        ], $creator);

        $this->assertSame(ChampionshipStatus::Draft, $first->status);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Plano Free permite apenas 1 campeonato league ativo por vez.');

        $service->create([
            'name' => 'Liga 2',
            'format' => ChampionshipFormat::League,
            'category_id' => $category->id,
            'sport_mode_ids' => [$sportMode->id],
        ], $creator);
    }

    public function test_can_open_enrollment_enroll_teams_select_players_and_activate_league(): void
    {
        $creator = User::factory()->create();
        $category = Category::factory()->create();
        $sportMode = SportMode::factory()->create();

        $championshipService = new ChampionshipService;
        $enrollmentService = new ChampionshipEnrollmentService;

        $championship = $championshipService->create([
            'name' => 'Liga de Bairro',
            'description' => 'Pontos corridos',
            'format' => ChampionshipFormat::League,
            'category_id' => $category->id,
            'sport_mode_ids' => [$sportMode->id],
            'max_players' => 2,
        ], $creator);

        $championship = $championshipService->openEnrollment($championship);

        $teamSportModes = collect([
            $this->createTeamSportMode($sportMode),
            $this->createTeamSportMode($sportMode),
            $this->createTeamSportMode($sportMode),
        ]);

        foreach ($teamSportModes as $teamSportMode) {
            $enrollmentService->enroll($championship, $teamSportMode);

            $memberships = collect([
                PlayerMembership::factory()->create(['team_sport_mode_id' => $teamSportMode->id]),
                PlayerMembership::factory()->create(['team_sport_mode_id' => $teamSportMode->id]),
            ]);

            $enrollmentService->selectPlayers($championship, $teamSportMode, $memberships->pluck('id')->all());
        }

        $championship = $championshipService->activate($championship->fresh());

        $this->assertSame(ChampionshipStatus::Active, $championship->status);
        $this->assertDatabaseCount('championship_teams', 3);
        $this->assertDatabaseCount('championship_team_players', 6);
        $this->assertDatabaseHas('championship_phases', [
            'championship_id' => $championship->id,
            'phase_order' => 1,
        ]);
        $this->assertSame(3, $championship->phases()->firstOrFail()->rounds()->count());
        $this->assertSame(3, $championship->phases()->firstOrFail()->rounds()->withCount('matches')->get()->sum('matches_count'));
    }

    public function test_registering_results_and_highlights_can_finish_championship_and_grant_awards_and_badges(): void
    {
        BadgeType::factory()->create(['name' => 'golden_ball', 'scope' => BadgeScope::Championship]);
        BadgeType::factory()->create(['name' => 'top_scorer', 'scope' => BadgeScope::Championship]);
        BadgeType::factory()->create(['name' => 'best_assist', 'scope' => BadgeScope::Championship]);
        BadgeType::factory()->create(['name' => 'fair_play', 'scope' => BadgeScope::Championship]);
        BadgeType::factory()->create(['name' => 'hat_trick', 'scope' => BadgeScope::Career]);

        $creator = User::factory()->create();
        $category = Category::factory()->create();
        $sportMode = SportMode::factory()->create();

        $championshipService = new ChampionshipService;
        $enrollmentService = new ChampionshipEnrollmentService;
        $matchService = new ChampionshipMatchService(new ChampionshipClosingService);

        $championship = $championshipService->create([
            'name' => 'Copa do Bairro',
            'format' => ChampionshipFormat::League,
            'category_id' => $category->id,
            'sport_mode_ids' => [$sportMode->id],
            'max_players' => 1,
        ], $creator);

        $championship = $championshipService->openEnrollment($championship);

        $homeTeam = $this->createTeamSportMode($sportMode);
        $awayTeam = $this->createTeamSportMode($sportMode);
        $thirdTeam = $this->createTeamSportMode($sportMode);

        $homeMembership = PlayerMembership::factory()->create(['team_sport_mode_id' => $homeTeam->id]);
        $awayMembership = PlayerMembership::factory()->create(['team_sport_mode_id' => $awayTeam->id]);
        $thirdMembership = PlayerMembership::factory()->create(['team_sport_mode_id' => $thirdTeam->id]);

        foreach ([[$homeTeam, $homeMembership], [$awayTeam, $awayMembership], [$thirdTeam, $thirdMembership]] as [$teamSportMode, $membership]) {
            $enrollmentService->enroll($championship, $teamSportMode);
            $enrollmentService->selectPlayers($championship, $teamSportMode, [$membership->id]);
        }

        $championship = $championshipService->activate($championship->fresh());

        $matches = $championship->phases()->firstOrFail()
            ->rounds()
            ->with('matches')
            ->get()
            ->flatMap->matches
            ->values();

        $firstMatch = $matches->firstWhere('home_team_id', $homeTeam->id) ?? $matches->first();
        $otherMatches = $matches->where('id', '!=', $firstMatch->id)->values();
        $awayMembershipForFirstMatch = match ($firstMatch->away_team_id) {
            $awayTeam->id => $awayMembership,
            $thirdTeam->id => $thirdMembership,
            default => throw new \RuntimeException('Partida selecionada não possui adversário esperado para o teste.'),
        };

        $matchService->registerResult($firstMatch, [
            'home_goals' => 3,
            'away_goals' => 1,
            'location' => 'Campo Principal',
        ]);

        $matchService->registerHighlights($firstMatch->fresh(), [
            [
                'player_membership_id' => $homeMembership->id,
                'goals' => 3,
                'assists' => 1,
                'yellow_cards' => 0,
                'red_cards' => 0,
                'is_mvp' => true,
            ],
            [
                'player_membership_id' => $awayMembershipForFirstMatch->id,
                'goals' => 1,
                'assists' => 0,
                'yellow_cards' => 0,
                'red_cards' => 0,
                'is_mvp' => false,
            ],
        ]);

        foreach ($otherMatches as $match) {
            $matchService->cancelMatch($match);
        }

        $championship->refresh();

        $this->assertSame(ChampionshipStatus::Finished, $championship->status);
        $this->assertDatabaseHas('championship_awards', [
            'championship_id' => $championship->id,
            'player_id' => $homeMembership->player_id,
            'award_type' => 'top_scorer',
        ]);
        $this->assertDatabaseHas('championship_awards', [
            'championship_id' => $championship->id,
            'player_id' => $homeMembership->player_id,
            'award_type' => 'best_assist',
        ]);
        $this->assertDatabaseHas('championship_awards', [
            'championship_id' => $championship->id,
            'player_id' => $homeMembership->player_id,
            'award_type' => 'golden_ball',
        ]);
        $this->assertDatabaseHas('player_badges', [
            'championship_id' => $championship->id,
            'player_id' => $homeMembership->player_id,
        ]);

        $awards = ChampionshipAward::query()->where('championship_id', $championship->id)->get();
        $badges = PlayerBadge::query()->where('championship_id', $championship->id)->get();

        $this->assertGreaterThanOrEqual(3, $awards->count());
        $this->assertGreaterThanOrEqual(4, $badges->count());
        $this->assertTrue($badges->contains('player_id', $homeMembership->player_id));
    }

    private function createTeamSportMode(SportMode $sportMode): TeamSportMode
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $owner->id]);

        return TeamSportMode::factory()->create([
            'team_id' => $team->id,
            'sport_mode_id' => $sportMode->id,
        ]);
    }
}
