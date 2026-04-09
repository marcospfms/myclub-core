<?php

namespace Database\Factories;

use App\Models\SportMode;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SportMode>
 */
class SportModeFactory extends Factory
{
    protected $model = SportMode::class;

    public function definition(): array
    {
        $name = fake()->unique()->word();
        $key = Str::of($name)->lower()->slug('_')->value();

        return [
            'key' => $key,
            'label_key' => "sport_modes.{$key}.label",
            'description_key' => "sport_modes.{$key}.description",
            'icon' => fake()->randomElement(['map', 'shield', 'star', 'trophy']),
        ];
    }
}
