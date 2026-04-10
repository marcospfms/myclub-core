<?php

namespace Database\Factories;

use App\Enums\ChampionshipFormat;
use App\Enums\ChampionshipStatus;
use App\Models\Category;
use App\Models\Championship;
use App\Models\SportMode;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Championship>
 */
class ChampionshipFactory extends Factory
{
    protected $model = Championship::class;

    public function definition(): array
    {
        return [
            'created_by' => User::factory(),
            'category_id' => Category::factory(),
            'name' => fake()->unique()->words(3, true).' Cup',
            'description' => fake()->sentence(),
            'location' => fake()->city(),
            'starts_at' => now()->addWeek()->toDateString(),
            'ends_at' => now()->addWeeks(2)->toDateString(),
            'format' => ChampionshipFormat::League,
            'status' => ChampionshipStatus::Draft,
            'max_players' => 20,
        ];
    }

    public function draft(): static
    {
        return $this->state([
            'status' => ChampionshipStatus::Draft,
        ]);
    }

    public function enrollment(): static
    {
        return $this->state([
            'status' => ChampionshipStatus::Enrollment,
        ]);
    }

    public function active(): static
    {
        return $this->state([
            'status' => ChampionshipStatus::Active,
        ]);
    }

    public function finished(): static
    {
        return $this->state([
            'status' => ChampionshipStatus::Finished,
        ]);
    }

    public function withSportMode(?SportMode $sportMode = null): static
    {
        return $this->afterCreating(function (Championship $championship) use ($sportMode): void {
            $mode = $sportMode ?? SportMode::factory()->create();

            $championship->sportModes()->syncWithoutDetaching([$mode->id]);
        });
    }
}
