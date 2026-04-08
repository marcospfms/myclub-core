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

        DB::table('sport_modes')->insert([
            ['key' => 'campo', 'name' => 'Campo', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'quadra', 'name' => 'Quadra', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'society', 'name' => 'Society', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'areia', 'name' => 'Areia', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
