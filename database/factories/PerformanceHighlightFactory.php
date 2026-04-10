<?php

namespace Database\Factories;

use App\Models\FriendlyMatch;
use App\Models\PerformanceHighlight;
use App\Models\PlayerMembership;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PerformanceHighlight>
 */
class PerformanceHighlightFactory extends Factory
{
    protected $model = PerformanceHighlight::class;

    public function definition(): array
    {
        return [
            'friendly_match_id' => FriendlyMatch::factory(),
            'player_membership_id' => PlayerMembership::factory(),
            'goals' => 0,
            'assists' => 0,
            'yellow_cards' => 0,
            'red_cards' => 0,
        ];
    }
}
