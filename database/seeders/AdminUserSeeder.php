<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the application's default admin user.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@myclub.app'],
            [
                'name' => 'MyClub Admin',
                'role' => 'admin',
                'email_verified_at' => now(),
                'password' => 'teste123',
            ],
        );
    }
}
