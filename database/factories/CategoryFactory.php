<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name'    => fake()->word(),
            'type'    => fake()->randomElement(['income', 'expense']),
            'color'   => '#6366f1',
            'icon'    => 'tag',
        ];
    }
}
