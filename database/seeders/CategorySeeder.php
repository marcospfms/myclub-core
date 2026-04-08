<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        DB::table('categories')->upsert([
            ['key' => 'livre', 'name' => 'Livre', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'sub_15', 'name' => 'Sub-15', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'sub_17', 'name' => 'Sub-17', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'sub_20', 'name' => 'Sub-20', 'created_at' => $now, 'updated_at' => $now],
        ], ['key'], ['name', 'updated_at']);
    }
}
