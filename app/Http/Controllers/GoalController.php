<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGoalRequest;
use App\Http\Requests\UpdateGoalRequest;
use App\Models\Goal;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoalController extends AbstractController
{
    /**
     * List all savings goals for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $goals = $request->user()
            ->goals()
            ->with(['linkedAccount'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($goals);
    }

    /**
     * Create a new savings goal.
     */
    public function store(StoreGoalRequest $request): JsonResponse
    {
        $data = $request->validated();
        $goal = $request->user()->goals()->create($data);

        return response()->json($goal->load('linkedAccount'), 201);
    }

    /**
     * Update an existing savings goal.
     */
    public function update(UpdateGoalRequest $request, Goal $goal): JsonResponse
    {
        abort_if($goal->user_id !== $request->user()->id, 403);

        $data = $request->validated();
        $goal->update($data);

        return response()->json($goal->fresh('linkedAccount'));
    }

    /**
     * Delete a savings goal.
     */
    public function destroy(Request $request, Goal $goal): JsonResponse
    {
        abort_if($goal->user_id !== $request->user()->id, 403);

        $goal->delete();

        return response()->json(['message' => 'Goal deleted successfully']);
    }

    /**
     * Deposit/add funds directly to a goal's current saved amount.
     */
    public function deposit(Request $request, Goal $goal, TransactionService $service): JsonResponse
    {
        abort_if($goal->user_id !== $request->user()->id, 403);

        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'from_account_id' => 'required|integer|exists:accounts,id',
        ]);

        abort_if($goal->linked_account_id == null, 422, 'Link a savings account to deposit into this goal.');
        abort_if((int) $data['from_account_id'] === (int) $goal->linked_account_id, 422, 'Choose a different source account.');

        $transaction = $service->createTransaction($request->user(), [
            'account_id' => $data['from_account_id'],
            'to_account_id' => $goal->linked_account_id,
            'type' => 'transfer',
            'amount' => (float) $data['amount'],
            'description' => 'Deposit to '.$goal->name,
            'date' => now()->toDateString(),
        ]);

        return response()->json([
            'message' => 'Deposit added to goal',
            'goal' => $goal->fresh('linkedAccount'),
            'transaction' => $transaction->load(['account', 'toAccount', 'category']),
        ]);
    }
}
