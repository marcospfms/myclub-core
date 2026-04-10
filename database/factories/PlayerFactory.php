<?php

namespace Database\Factories;

use App\Models\Player;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Player>
 */
class PlayerFactory extends Factory
{
    protected $model = Player::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'cpf' => fake()->unique()->numerify('###########'),
            'rg' => fake()->numerify('#########'),
            'birth_date' => fake()->dateTimeBetween('-40 years', '-14 years')->format('Y-m-d'),
            'phone' => fake()->numerify('###########'),
            'is_discoverable' => false,
            'history_public' => false,
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'country' => 'BR',
        ];
    }
}
