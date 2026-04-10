<?php

namespace Database\Factories;

use App\Models\ChampionshipGroup;
use App\Models\ChampionshipPhase;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChampionshipGroup>
 */
class ChampionshipGroupFactory extends Factory
{
    protected $model = ChampionshipGroup::class;

    public function definition(): array
    {
        return [
            'championship_phase_id' => ChampionshipPhase::factory(),
            'name' => 'Geral',
        ];
    }
}
