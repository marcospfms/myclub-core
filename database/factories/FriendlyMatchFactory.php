<?php

namespace Database\Factories;

use App\Enums\MatchConfirmation;
use App\Enums\MatchStatus;
use App\Enums\ResultStatus;
use App\Models\FriendlyMatch;
use App\Models\TeamSportMode;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FriendlyMatch>
 */
class FriendlyMatchFactory extends Factory
{
    protected $model = FriendlyMatch::class;

    public function definition(): array
    {
        return [
            'home_team_id' => TeamSportMode::factory(),
            'away_team_id' => function (array $attributes): int {
                $homeTeamSportMode = TeamSportMode::query()->with('team')->findOrFail($attributes['home_team_id']);

                return TeamSportMode::factory()->create([
                    'sport_mode_id' => $homeTeamSportMode->sport_mode_id,
                ])->id;
            },
            'scheduled_at' => now()->addDays(7),
            'location' => fake()->address(),
            'confirmation' => MatchConfirmation::Pending,
            'invite_expires_at' => now()->addDays(2),
            'match_status' => null,
            'home_goals' => null,
            'away_goals' => null,
            'home_notes' => null,
            'away_notes' => null,
            'is_public' => false,
            'result_status' => ResultStatus::None,
            'result_registered_by' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'confirmation' => MatchConfirmation::Pending,
            'match_status' => null,
            'result_status' => ResultStatus::None,
            'result_registered_by' => null,
            'home_goals' => null,
            'away_goals' => null,
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'confirmation' => MatchConfirmation::Confirmed,
            'match_status' => MatchStatus::Scheduled,
            'result_status' => ResultStatus::None,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'confirmation' => MatchConfirmation::Confirmed,
            'match_status' => MatchStatus::Completed,
            'result_status' => ResultStatus::Confirmed,
            'home_goals' => fake()->numberBetween(0, 5),
            'away_goals' => fake()->numberBetween(0, 5),
        ]);
    }

    public function withPendingResult(User $registeredBy): static
    {
        return $this->state(fn (array $attributes) => [
            'confirmation' => MatchConfirmation::Confirmed,
            'match_status' => MatchStatus::Scheduled,
            'result_status' => ResultStatus::Pending,
            'home_goals' => 2,
            'away_goals' => 1,
            'result_registered_by' => $registeredBy->id,
        ]);
    }
}
