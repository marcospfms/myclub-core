<?php

namespace Database\Factories;

use App\Enums\MatchStatus;
use App\Enums\PhaseType;
use App\Models\Championship;
use App\Models\ChampionshipMatch;
use App\Models\ChampionshipPhase;
use App\Models\ChampionshipRound;
use App\Models\TeamSportMode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChampionshipMatch>
 */
class ChampionshipMatchFactory extends Factory
{
    protected $model = ChampionshipMatch::class;

    public function definition(): array
    {
        return [
            'championship_round_id' => ChampionshipRound::factory(),
            'home_team_id' => TeamSportMode::factory(),
            'away_team_id' => TeamSportMode::factory(),
            'scheduled_at' => now()->addDays(7),
            'location' => fake()->city(),
            'match_status' => MatchStatus::Scheduled,
            'home_goals' => null,
            'away_goals' => null,
            'home_penalties' => null,
            'away_penalties' => null,
            'leg' => 1,
        ];
    }

    public function scheduled(): static
    {
        return $this->state([
            'match_status' => MatchStatus::Scheduled,
            'home_goals' => null,
            'away_goals' => null,
            'home_penalties' => null,
            'away_penalties' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state([
            'match_status' => MatchStatus::Completed,
            'home_goals' => fake()->numberBetween(0, 4),
            'away_goals' => fake()->numberBetween(0, 4),
        ]);
    }

    public function forChampionship(Championship $championship): static
    {
        return $this->afterCreating(function (ChampionshipMatch $match) use ($championship): void {
            $phase = $championship->phases()->first() ?? ChampionshipPhase::factory()->create([
                'championship_id' => $championship->id,
                'name' => 'Fase Principal',
                'type' => PhaseType::GroupStage,
                'phase_order' => 1,
                'legs' => 1,
                'advances_count' => 0,
            ]);

            $round = $phase->rounds()->first() ?? ChampionshipRound::factory()->create([
                'championship_phase_id' => $phase->id,
                'name' => 'Rodada 1',
                'round_number' => 1,
            ]);

            $match->update([
                'championship_round_id' => $round->id,
            ]);
        });
    }
}
