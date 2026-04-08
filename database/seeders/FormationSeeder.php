<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FormationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        DB::table('formations')->insert([
            ['key' => '4-4-2', 'name' => '4-4-2', 'created_at' => $now, 'updated_at' => $now],
            ['key' => '4-3-3', 'name' => '4-3-3', 'created_at' => $now, 'updated_at' => $now],
            ['key' => '4-5-1', 'name' => '4-5-1', 'created_at' => $now, 'updated_at' => $now],
            ['key' => '3-5-2', 'name' => '3-5-2', 'created_at' => $now, 'updated_at' => $now],
            ['key' => '3-4-3', 'name' => '3-4-3', 'created_at' => $now, 'updated_at' => $now],
            ['key' => '3-6-1', 'name' => '3-6-1', 'created_at' => $now, 'updated_at' => $now],
            ['key' => '1-2-1', 'name' => '1-2-1', 'created_at' => $now, 'updated_at' => $now],
            ['key' => '2-2-1', 'name' => '2-2-1', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
