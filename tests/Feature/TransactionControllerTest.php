<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyClerkToken;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    private TransactionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyClerkToken::class);
        $this->service = new TransactionService;
    }

    public function test_user_can_list_their_transactions(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        Transaction::factory()->count(3)->for($user)->create(['account_id' => $account->id]);

        $otherUser = User::factory()->create();
        $otherAccount = Account::factory()->for($otherUser)->create();
        Transaction::factory()->count(2)->for($otherUser)->create(['account_id' => $otherAccount->id]);

        $response = $this->actingAs($user)->getJson('/api/transactions');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_create_transaction_and_adjust_balance(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create(['balance' => 500]);
        $category = Category::factory()->for($user)->create(['type' => 'expense']);

        $payload = [
            'account_id' => $account->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 150,
            'date' => now()->toDateString(),
            'description' => 'Groceries',
        ];

        $response = $this->actingAs($user)->postJson('/api/transactions', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('amount', '150.00');

        $this->assertEquals(350, $account->fresh()->balance);
    }

    public function test_user_can_update_transaction(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create(['balance' => 500]);

        $transaction = $this->service->createTransaction($user, [
            'account_id' => $account->id,
            'type' => 'expense',
            'amount' => 100,
            'date' => now()->toDateString(),
        ]);

        $this->assertEquals(400, $account->fresh()->balance);

        $payload = [
            'account_id' => $account->id,
            'type' => 'expense',
            'amount' => 50,
            'date' => now()->toDateString(),
            'description' => 'Updated expense',
        ];

        $response = $this->actingAs($user)->putJson('/api/transactions/'.$transaction->id, $payload);

        $response->assertStatus(200)
            ->assertJsonPath('description', 'Updated expense');

        $this->assertEquals(450, $account->fresh()->balance);
    }

    public function test_user_can_delete_transaction(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create(['balance' => 500]);

        $transaction = $this->service->createTransaction($user, [
            'account_id' => $account->id,
            'type' => 'expense',
            'amount' => 100,
            'date' => now()->toDateString(),
        ]);

        $this->assertEquals(400, $account->fresh()->balance);

        $response = $this->actingAs($user)->deleteJson('/api/transactions/'.$transaction->id);

        $response->assertStatus(200);

        $this->assertEquals(500, $account->fresh()->balance);
        $this->assertSoftDeleted('transactions', ['id' => $transaction->id]);
    }

    public function test_user_cannot_attach_another_users_category_idor_prevention(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $accountA = Account::factory()->for($userA)->create(['balance' => 500]);
        $categoryB = Category::factory()->for($userB)->create(['type' => 'expense']);

        $payload = [
            'account_id' => $accountA->id,
            'category_id' => $categoryB->id, // Belongs to userB!
            'type' => 'expense',
            'amount' => 50,
            'date' => now()->toDateString(),
        ];

        $response = $this->actingAs($userA)->postJson('/api/transactions', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category_id']);
    }

    public function test_user_cannot_create_transaction_against_another_users_account(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $accountB = Account::factory()->for($userB)->create(['balance' => 500]);

        $payload = [
            'account_id' => $accountB->id, // Belongs to userB!
            'type' => 'expense',
            'amount' => 50,
            'date' => now()->toDateString(),
        ];

        $response = $this->actingAs($userA)->postJson('/api/transactions', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['account_id']);
    }

    public function test_user_cannot_update_another_users_transaction(): void
    {
        $userA = User::factory()->create();
        $accountA = Account::factory()->for($userA)->create();

        $userB = User::factory()->create();
        $accountB = Account::factory()->for($userB)->create();
        $transactionB = Transaction::factory()->for($userB)->create(['account_id' => $accountB->id]);

        $payload = [
            'account_id' => $accountA->id,
            'type' => 'expense',
            'amount' => 99,
            'date' => now()->toDateString(),
            'description' => 'Hacked update',
        ];

        $response = $this->actingAs($userA)->putJson('/api/transactions/'.$transactionB->id, $payload);

        $response->assertStatus(403);
    }
}
