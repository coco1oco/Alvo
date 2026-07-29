<?php

namespace Tests\Unit;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialPrecisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_cumulative_decimal_operations_have_zero_float_drift(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create(['balance' => '0.00']);

        // Perform 1,000 additions of $0.10, $0.20, $0.30
        for ($i = 0; $i < 1000; $i++) {
            $account->adjustBalance('0.10');
            $account->adjustBalance('0.20');
            $account->adjustBalance('-0.10');
        }

        // Expected balance: 1000 * (0.10 + 0.20 - 0.10) = 200.00 exactly
        $this->assertEquals('200.00', (string) $account->fresh()->balance);
    }
}
