<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyClerkToken;
use App\Models\Account;
use App\Models\Goal;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoalControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyClerkToken::class);
    }

    public function test_goal_deposit_creates_a_transfer_transaction_with_a_goal_description(): void
    {
        $user = User::factory()->create();
        $sourceAccount = Account::factory()->for($user)->create(['balance' => 500]);
        $savingsAccount = Account::factory()->for($user)->create(['balance' => 120]);

        $goal = Goal::query()->create([
            'user_id' => $user->id,
            'linked_account_id' => $savingsAccount->id,
            'name' => 'Emergency Fund',
            'target_amount' => 1000,
            'current_amount' => 0,
            'deadline' => null,
            'color' => '#6366f1',
            'icon' => 'target',
        ]);

        $response = $this->actingAs($user)->postJson('/api/goals/'.$goal->id.'/deposit', [
            'amount' => 75,
            'from_account_id' => $sourceAccount->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('transaction.type', 'transfer')
            ->assertJsonPath('transaction.description', 'Deposit to Emergency Fund')
            ->assertJsonPath('transaction.account_id', $sourceAccount->id)
            ->assertJsonPath('transaction.to_account_id', $savingsAccount->id);

        $this->assertEquals('425.00', $sourceAccount->fresh()->balance);
        $this->assertEquals('195.00', $savingsAccount->fresh()->balance);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'account_id' => $sourceAccount->id,
            'to_account_id' => $savingsAccount->id,
            'type' => 'transfer',
            'description' => 'Deposit to Emergency Fund',
            'amount' => '75.00',
        ]);

        $this->assertEquals(1, Transaction::query()->count());
    }
}