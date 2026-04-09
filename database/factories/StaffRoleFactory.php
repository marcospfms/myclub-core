<?php

namespace Database\Factories;

use App\Models\StaffRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<StaffRole>
 */
class StaffRoleFactory extends Factory
{
    protected $model = StaffRole::class;

    public function definition(): array
    {
        $name = Str::of(fake()->unique()->words(2, true))->lower()->slug('_')->value();

        return [
            'name' => $name,
            'label_key' => "staff_roles.{$name}.label",
            'description_key' => "staff_roles.{$name}.description",
            'icon' => fake()->randomElement(['users', 'briefcase', 'whistle', 'shield']),
        ];
    }
}
