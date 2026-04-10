<?php

namespace Tests\Feature\Phase3;

use App\Enums\AwardType;
use App\Enums\ChampionshipFormat;
use App\Enums\ChampionshipStatus;
use App\Enums\MatchStatus;
use App\Enums\PhaseType;
use App\Models\BadgeType;
use App\Models\Category;
use App\Models\Championship;
use App\Models\ChampionshipAward;
use App\Models\ChampionshipGroup;
use App\Models\ChampionshipGroupEntry;
use App\Models\ChampionshipMatch;
use App\Models\ChampionshipMatchHighlight;
use App\Models\ChampionshipPhase;
use App\Models\ChampionshipRound;
use App\Models\ChampionshipTeam;
use App\Models\ChampionshipTeamPlayer;
use App\Models\Player;
use App\Models\PlayerBadge;
use App\Models\PlayerMembership;
use App\Models\SportMode;
use App\Models\TeamSportMode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ChampionshipSchemaAndModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_phase_three_tables_exist(): void
    {
        $tables = [
            'championships',
            'championship_sport_modes',
            'championship_phases',
            'championship_groups',
            'championship_rounds',
            'championship_teams',
            'championship_group_entries',
            'championship_team_players',
            'championship_matches',
            'championship_match_highlights',
            'championship_awards',
            'player_badges',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(Schema::hasTable($table), "Expected table [{$table}] to exist.");
        }
    }

    public function test_championship_core_models_cast_enums_and_relations(): void
    {
        $creator = User::factory()->create();
        $category = Category::factory()->create();
        $sportMode = SportMode::factory()->create();

        $championship = Championship::create([
            'created_by' => $creator->id,
            'category_id' => $category->id,
            'name' => 'Copa Manaus',
            'description' => 'Campeonato de pontos corridos',
            'location' => 'Arena Norte',
            'starts_at' => '2026-05-01',
            'ends_at' => '2026-05-30',
            'format' => ChampionshipFormat::League,
            'status' => ChampionshipStatus::Draft,
            'max_players' => 20,
        ]);

        $championship->sportModes()->attach($sportMode->id);

        $phase = ChampionshipPhase::create([
            'championship_id' => $championship->id,
            'name' => 'Fase Principal',
            'type' => PhaseType::GroupStage,
            'phase_order' => 1,
            'legs' => 1,
            'advances_count' => 0,
        ]);

        $group = ChampionshipGroup::create([
            'championship_phase_id' => $phase->id,
            'name' => 'Geral',
        ]);

        $round = ChampionshipRound::create([
            'championship_phase_id' => $phase->id,
            'name' => 'Rodada 1',
            'round_number' => 1,
        ]);

        $homeTeam = TeamSportMode::factory()->create();
        $awayTeam = TeamSportMode::factory()->create([
            'sport_mode_id' => $homeTeam->sport_mode_id,
        ]);

        $entry = ChampionshipTeam::create([
            'championship_id' => $championship->id,
            'team_sport_mode_id' => $homeTeam->id,
        ]);

        $groupEntry = ChampionshipGroupEntry::create([
            'championship_group_id' => $group->id,
            'team_sport_mode_id' => $homeTeam->id,
            'final_position' => 1,
        ]);

        $membership = PlayerMembership::factory()->create([
            'team_sport_mode_id' => $homeTeam->id,
        ]);

        $selection = ChampionshipTeamPlayer::create([
            'championship_id' => $championship->id,
            'team_sport_mode_id' => $homeTeam->id,
            'player_membership_id' => $membership->id,
        ]);

        $match = ChampionshipMatch::create([
            'championship_round_id' => $round->id,
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
            'scheduled_at' => now(),
            'location' => 'Arena Norte',
            'match_status' => MatchStatus::Scheduled,
            'leg' => 1,
        ]);

        $highlight = ChampionshipMatchHighlight::create([
            'championship_match_id' => $match->id,
            'player_membership_id' => $membership->id,
            'goals' => 2,
            'assists' => 1,
            'yellow_cards' => 0,
            'red_cards' => 0,
            'is_mvp' => true,
        ]);

        $award = ChampionshipAward::create([
            'championship_id' => $championship->id,
            'player_id' => $membership->player_id,
            'award_type' => AwardType::TopScorer,
            'value' => 2,
        ]);

        $badgeType = BadgeType::factory()->create();

        $badge = PlayerBadge::create([
            'player_id' => $membership->player_id,
            'badge_type_id' => $badgeType->id,
            'championship_id' => $championship->id,
            'awarded_at' => now(),
            'notes' => 'Artilheiro da Copa Manaus',
            'year' => 2026,
        ]);

        $championship->refresh();
        $phase->refresh();
        $match->refresh();
        $award->refresh();
        $highlight->refresh();
        $badge->refresh();

        $this->assertInstanceOf(ChampionshipFormat::class, $championship->format);
        $this->assertInstanceOf(ChampionshipStatus::class, $championship->status);
        $this->assertInstanceOf(PhaseType::class, $phase->type);
        $this->assertInstanceOf(MatchStatus::class, $match->match_status);
        $this->assertInstanceOf(AwardType::class, $award->award_type);

        $this->assertTrue($championship->isDraft());
        $this->assertSame($creator->id, $championship->creator->id);
        $this->assertSame($category->id, $championship->category->id);
        $this->assertCount(1, $championship->sportModes);
        $this->assertCount(1, $championship->phases);
        $this->assertCount(1, $championship->teams);
        $this->assertCount(1, $championship->awards);
        $this->assertCount(1, $phase->groups);
        $this->assertCount(1, $phase->rounds);
        $this->assertCount(1, $group->entries);
        $this->assertCount(1, $round->matches);
        $this->assertCount(1, $entry->players);
        $this->assertCount(1, $match->highlights);
        $this->assertTrue($match->scheduled_at !== null);
        $this->assertTrue($highlight->is_mvp);
        $this->assertSame($membership->id, $selection->membership->id);
        $this->assertSame($homeTeam->id, $groupEntry->teamSportMode->id);
        $this->assertSame($membership->player_id, $award->player->user_id);
        $this->assertSame($championship->id, $badge->championship->id);
        $this->assertSame($badgeType->id, $badge->badgeType->id);
    }
}
