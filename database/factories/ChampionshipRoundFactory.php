<?php

namespace Database\Factories;

use App\Models\ChampionshipPhase;
use App\Models\ChampionshipRound;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChampionshipRound>
 */
class ChampionshipRoundFactory extends Factory
{
    protected $model = ChampionshipRound::class;

    public function definition(): array
    {
        return [
            'championship_phase_id' => ChampionshipPhase::factory(),
            'name' => 'Rodada 1',
            'round_number' => 1,
        ];
    }
}
