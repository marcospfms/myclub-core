<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SportModeFormationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        DB::table('sport_mode_formation')->delete();

        $sportModeIds = DB::table('sport_modes')->pluck('id', 'key');
        $formationIds = DB::table('formations')->pluck('id', 'key');
        $rows = [];
        $fieldFormationKeys = ['4-4-2', '4-3-3', '4-5-1', '3-5-2', '3-4-3', '3-6-1'];
        $futsalFormationKeys = ['1-2-1', '2-2-1'];

        foreach (['campo', 'society'] as $sportModeKey) {
            foreach ($fieldFormationKeys as $formationKey) {
                $rows[] = [
                    'sport_mode_id' => $sportModeIds[$sportModeKey],
                    'formation_id' => $formationIds[$formationKey],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (['quadra', 'areia'] as $sportModeKey) {
            foreach ($futsalFormationKeys as $formationKey) {
                $rows[] = [
                    'sport_mode_id' => $sportModeIds[$sportModeKey],
                    'formation_id' => $formationIds[$formationKey],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('sport_mode_formation')->insert($rows);
    }
}
