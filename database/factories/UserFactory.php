<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'clerk_id' => 'user_' . fake()->unique()->regexify('[A-Za-z0-9]{20}'),
            'name'     => fake()->name(),
            'email'    => fake()->unique()->safeEmail(),
        ];
    }
}
