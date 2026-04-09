<?php

namespace Database\Factories;

use App\Models\Formation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Formation>
 */
class FormationFactory extends Factory
{
    protected $model = Formation::class;

    public function definition(): array
    {
        $segments = [
            fake()->numberBetween(1, 4),
            fake()->numberBetween(1, 5),
            fake()->numberBetween(1, 4),
        ];

        $name = implode('-', $segments);

        return [
            'key' => $name,
            'name' => $name,
        ];
    }
}
