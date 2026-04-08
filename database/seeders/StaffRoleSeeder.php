<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StaffRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        DB::table('staff_roles')->insert([
            ['name' => 'head_coach', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'assistant_coach', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'physical_trainer', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'goalkeeping_coach', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'scout', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'analyst', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'physiotherapist', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'doctor', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'other', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
