<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SportModeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        DB::table('sport_modes')->upsert([
            ['key' => 'campo', 'label_key' => 'sport_modes.campo.label', 'description_key' => 'sport_modes.campo.description', 'icon' => 'map', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'quadra', 'label_key' => 'sport_modes.quadra.label', 'description_key' => 'sport_modes.quadra.description', 'icon' => 'square', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'society', 'label_key' => 'sport_modes.society.label', 'description_key' => 'sport_modes.society.description', 'icon' => 'shield', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'areia', 'label_key' => 'sport_modes.areia.label', 'description_key' => 'sport_modes.areia.description', 'icon' => 'sun', 'created_at' => $now, 'updated_at' => $now],
        ], ['key'], ['label_key', 'description_key', 'icon', 'updated_at']);
    }
}
