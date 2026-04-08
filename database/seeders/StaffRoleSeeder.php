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

        DB::table('staff_roles')->upsert([
            ['name' => 'head_coach', 'label_key' => 'staff_roles.head_coach.label', 'description_key' => 'staff_roles.head_coach.description', 'icon' => 'whistle', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'assistant_coach', 'label_key' => 'staff_roles.assistant_coach.label', 'description_key' => 'staff_roles.assistant_coach.description', 'icon' => 'users', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'physical_trainer', 'label_key' => 'staff_roles.physical_trainer.label', 'description_key' => 'staff_roles.physical_trainer.description', 'icon' => 'activity', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'goalkeeping_coach', 'label_key' => 'staff_roles.goalkeeping_coach.label', 'description_key' => 'staff_roles.goalkeeping_coach.description', 'icon' => 'shield', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'scout', 'label_key' => 'staff_roles.scout.label', 'description_key' => 'staff_roles.scout.description', 'icon' => 'binoculars', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'analyst', 'label_key' => 'staff_roles.analyst.label', 'description_key' => 'staff_roles.analyst.description', 'icon' => 'bar-chart', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'physiotherapist', 'label_key' => 'staff_roles.physiotherapist.label', 'description_key' => 'staff_roles.physiotherapist.description', 'icon' => 'heart_pulse', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'doctor', 'label_key' => 'staff_roles.doctor.label', 'description_key' => 'staff_roles.doctor.description', 'icon' => 'cross', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'other', 'label_key' => 'staff_roles.other.label', 'description_key' => 'staff_roles.other.description', 'icon' => 'briefcase', 'created_at' => $now, 'updated_at' => $now],
        ], ['name'], ['label_key', 'description_key', 'icon', 'updated_at']);
    }
}
