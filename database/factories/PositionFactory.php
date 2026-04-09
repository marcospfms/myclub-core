<?php

namespace Database\Factories;

use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Position>
 */
class PositionFactory extends Factory
{
    protected $model = Position::class;

    public function definition(): array
    {
        $name = fake()->unique()->word();
        $key = Str::of($name)->lower()->slug('_')->value();

        return [
            'key' => $key,
            'label_key' => "positions.{$key}.label",
            'description_key' => "positions.{$key}.description",
            'icon' => fake()->randomElement(['shield', 'sparkles', 'zap', 'star']),
            'abbreviation' => Str::of($key)->upper()->substr(0, 3)->padRight(3, 'X')->value(),
        ];
    }
}
