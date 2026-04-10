<?php

namespace Database\Factories;

use App\Enums\PhaseType;
use App\Models\Championship;
use App\Models\ChampionshipPhase;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChampionshipPhase>
 */
class ChampionshipPhaseFactory extends Factory
{
    protected $model = ChampionshipPhase::class;

    public function definition(): array
    {
        return [
            'championship_id' => Championship::factory(),
            'name' => 'Fase Principal',
            'type' => PhaseType::GroupStage,
            'phase_order' => 1,
            'legs' => 1,
            'advances_count' => 0,
        ];
    }
}
