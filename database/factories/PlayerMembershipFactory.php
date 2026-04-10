<?php

namespace Database\Factories;

use App\Models\Player;
use App\Models\PlayerMembership;
use App\Models\Position;
use App\Models\TeamSportMode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlayerMembership>
 */
class PlayerMembershipFactory extends Factory
{
    protected $model = PlayerMembership::class;

    public function definition(): array
    {
        return [
            'team_sport_mode_id' => TeamSportMode::factory(),
            'player_id' => Player::factory(),
            'position_id' => Position::factory(),
            'is_starter' => false,
            'left_at' => null,
        ];
    }

    public function starter(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_starter' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'left_at' => now()->subDay(),
        ]);
    }
}
