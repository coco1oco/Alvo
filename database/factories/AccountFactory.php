<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'           => User::factory(),
            'name'              => fake()->words(2, true),
            'type'              => 'bank',
            'balance'           => 0,
            'credit_limit'      => null,
            'billing_cycle_day' => null,
            'due_date_day'      => null,
            'color'             => '#6366f1',
            'icon'              => 'wallet',
            'is_archived'       => false,
        ];
    }

    /**
     * Configure this account as a credit card with a debt-style balance.
     */
    public function creditCard(): static
    {
        return $this->state(fn () => [
            'type'              => 'credit_card',
            'credit_limit'      => 5000,
            'billing_cycle_day' => 1,
            'due_date_day'      => 15,
        ]);
    }
}
