<?php

namespace Database\Factories;

use App\Enums\BadgeScope;
use App\Models\BadgeType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<BadgeType>
 */
class BadgeTypeFactory extends Factory
{
    protected $model = BadgeType::class;

    public function definition(): array
    {
        $name = Str::of(fake()->unique()->words(2, true))->lower()->slug('_')->value();

        return [
            'name' => $name,
            'label_key' => "badges.{$name}.label",
            'description_key' => "badges.{$name}.description",
            'icon' => fake()->randomElement(['award', 'shield', 'star', 'zap']),
            'scope' => fake()->randomElement(BadgeScope::cases()),
        ];
    }
}
