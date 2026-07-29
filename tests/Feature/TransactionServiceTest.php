<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class TransactionServiceTest extends TestCase
{
    use RefreshDatabase;

    private TransactionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TransactionService;
    }

    public function test_expense_decreases_checking_account_balance(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create(['balance' => 100]);

        $this->service->createTransaction($user, [
            'account_id' => $account->id,
            'type' => 'expense',
            'amount' => 40,
            'date' => now()->toDateString(),
        ]);

        $this->assertEquals(60, $account->fresh()->balance);
    }

    public function test_income_increases_checking_account_balance(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create(['balance' => 100]);

        $this->service->createTransaction($user, [
            'account_id' => $account->id,
            'type' => 'income',
            'amount' => 25,
            'date' => now()->toDateString(),
        ]);

        $this->assertEquals(125, $account->fresh()->balance);
    }

    public function test_expense_on_credit_card_increases_debt(): void
    {
        $user = User::factory()->create();
        $card = Account::factory()->for($user)->creditCard()->create(['balance' => 0]);

        $this->service->createTransaction($user, [
            'account_id' => $card->id,
            'type' => 'expense',
            'amount' => 50,
            'date' => now()->toDateString(),
        ]);

        // Expense on a credit card raises outstanding debt, not lowers it.
        $this->assertEquals(50, $card->fresh()->balance);
    }

    public function test_income_on_credit_card_decreases_debt(): void
    {
        $user = User::factory()->create();
        $card = Account::factory()->for($user)->creditCard()->create(['balance' => 100]);

        $this->service->createTransaction($user, [
            'account_id' => $card->id,
            'type' => 'income',
            'amount' => 30,
            'date' => now()->toDateString(),
        ]);

        $this->assertEquals(70, $card->fresh()->balance);
    }

    public function test_transfer_moves_funds_between_two_checking_accounts(): void
    {
        $user = User::factory()->create();
        $from = Account::factory()->for($user)->create(['balance' => 200]);
        $to = Account::factory()->for($user)->create(['balance' => 50]);

        $this->service->createTransaction($user, [
            'account_id' => $from->id,
            'to_account_id' => $to->id,
            'type' => 'transfer',
            'amount' => 75,
            'date' => now()->toDateString(),
        ]);

        $this->assertEquals(125, $from->fresh()->balance);
        $this->assertEquals(125, $to->fresh()->balance);
    }

    public function test_transfer_to_credit_card_pays_down_debt_instead_of_crediting_balance(): void
    {
        $user = User::factory()->create();
        $checking = Account::factory()->for($user)->create(['balance' => 300]);
        $card = Account::factory()->for($user)->creditCard()->create(['balance' => 100]);

        $this->service->createTransaction($user, [
            'account_id' => $checking->id,
            'to_account_id' => $card->id,
            'type' => 'transfer',
            'amount' => 60,
            'date' => now()->toDateString(),
        ]);

        $this->assertEquals(240, $checking->fresh()->balance);
        $this->assertEquals(40, $card->fresh()->balance); // 100 debt - 60 payment
    }

    public function test_transfer_cannot_originate_from_a_credit_card(): void
    {
        $user = User::factory()->create();
        $card = Account::factory()->for($user)->creditCard()->create(['balance' => 100]);
        $checking = Account::factory()->for($user)->create(['balance' => 0]);

        $this->expectException(HttpException::class);

        $this->service->createTransaction($user, [
            'account_id' => $card->id,
            'to_account_id' => $checking->id,
            'type' => 'transfer',
            'amount' => 20,
            'date' => now()->toDateString(),
        ]);
    }

    public function test_deleting_a_transaction_reverses_its_balance_effect(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create(['balance' => 100]);

        $transaction = $this->service->createTransaction($user, [
            'account_id' => $account->id,
            'type' => 'expense',
            'amount' => 40,
            'date' => now()->toDateString(),
        ]);

        $this->assertEquals(60, $account->fresh()->balance);

        $this->service->deleteTransaction($user, $transaction);

        $this->assertEquals(100, $account->fresh()->balance);
    }

    public function test_updating_a_transaction_reapplies_the_new_balance_effect(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create(['balance' => 100]);

        $transaction = $this->service->createTransaction($user, [
            'account_id' => $account->id,
            'type' => 'expense',
            'amount' => 40,
            'date' => now()->toDateString(),
        ]);

        $this->assertEquals(60, $account->fresh()->balance);

        $this->service->updateTransaction($user, $transaction, [
            'account_id' => $account->id,
            'type' => 'expense',
            'amount' => 70,
            'date' => now()->toDateString(),
        ]);

        $this->assertEquals(30, $account->fresh()->balance);
    }

    public function test_a_user_cannot_create_a_transaction_against_another_users_account(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $victimAccount = Account::factory()->for($owner)->create(['balance' => 500]);

        $this->expectException(ModelNotFoundException::class);

        $this->service->createTransaction($attacker, [
            'account_id' => $victimAccount->id,
            'type' => 'expense',
            'amount' => 500,
            'date' => now()->toDateString(),
        ]);
    }
}
