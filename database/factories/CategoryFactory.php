<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);
        $key = Str::of($name)->lower()->slug('_')->value();

        return [
            'key' => $key,
            'name' => Str::of($name)->title()->value(),
        ];
    }
}
