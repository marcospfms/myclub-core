<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SportModeCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        DB::table('sport_mode_category')->delete();

        $sportModeIds = DB::table('sport_modes')->pluck('id');
        $categoryIds = DB::table('categories')->pluck('id');
        $rows = [];

        foreach ($sportModeIds as $sportModeId) {
            foreach ($categoryIds as $categoryId) {
                $rows[] = [
                    'sport_mode_id' => $sportModeId,
                    'category_id' => $categoryId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('sport_mode_category')->insert($rows);
    }
}
