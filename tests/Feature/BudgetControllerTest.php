<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyClerkToken;
use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyClerkToken::class);
    }

    public function test_user_can_update_a_budget_category_and_limit(): void
    {
        $user = User::factory()->create();
        $groceries = Category::factory()->for($user)->create(['type' => 'expense', 'name' => 'Groceries']);
        $transport = Category::factory()->for($user)->create(['type' => 'expense', 'name' => 'Transport']);

        $budget = Budget::query()->create([
            'user_id' => $user->id,
            'category_id' => $groceries->id,
            'amount' => 5000,
            'month' => '2026-07',
        ]);

        $response = $this->actingAs($user)->putJson('/api/budgets/'.$budget->id, [
            'category_id' => $transport->id,
            'amount' => 7500,
            'month' => '2026-07',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('category_id', $transport->id)
            ->assertJsonPath('amount', '7500.00');

        $this->assertDatabaseHas('budgets', [
            'id' => $budget->id,
            'category_id' => $transport->id,
            'amount' => '7500.00',
            'month' => '2026-07',
        ]);
    }
}