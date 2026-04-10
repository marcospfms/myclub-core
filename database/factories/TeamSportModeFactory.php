<?php

namespace Database\Factories;

use App\Models\SportMode;
use App\Models\Team;
use App\Models\TeamSportMode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeamSportMode>
 */
class TeamSportModeFactory extends Factory
{
    protected $model = TeamSportMode::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'sport_mode_id' => SportMode::factory(),
        ];
    }
}
