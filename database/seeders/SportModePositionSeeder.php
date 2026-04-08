<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SportModePositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $sportModeIds = DB::table('sport_modes')->pluck('id', 'key');
        $positionIds = DB::table('positions')->pluck('id', 'abbreviation');
        $rows = [];
        $fieldAbbreviations = ['GOL', 'ZC', 'LD', 'LE', 'VOL', 'ML', 'MLD', 'MLE', 'MAT', 'SA', 'PD', 'PE', 'ATA'];
        $futsalAbbreviations = ['GOL', 'FIX', 'ALD', 'ALE', 'PIV'];

        foreach (['campo', 'society'] as $sportModeKey) {
            foreach ($fieldAbbreviations as $abbreviation) {
                $rows[] = [
                    'sport_mode_id' => $sportModeIds[$sportModeKey],
                    'position_id' => $positionIds[$abbreviation],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (['quadra', 'areia'] as $sportModeKey) {
            foreach ($futsalAbbreviations as $abbreviation) {
                $rows[] = [
                    'sport_mode_id' => $sportModeIds[$sportModeKey],
                    'position_id' => $positionIds[$abbreviation],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('sport_mode_position')->insert($rows);
    }
}
