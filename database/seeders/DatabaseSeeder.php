<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SportModeSeeder::class,
            CategorySeeder::class,
            PositionSeeder::class,
            FormationSeeder::class,
            StaffRoleSeeder::class,
            BadgeTypeSeeder::class,
            SportModeCategorySeeder::class,
            SportModeFormationSeeder::class,
            SportModePositionSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
