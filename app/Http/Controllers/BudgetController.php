<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Http\Requests\StoreBudgetRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BudgetController extends AbstractController
{
    /**
     * List all budgets for the authenticated user for a given month.
     *
     * @param  Request $request the incoming HTTP request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $month = $request->get('month', now()->format('Y-m'));

        $budgets = $request->user()
            ->budgets()
            ->with('category')
            ->where('month', $month)
            ->get()
            ->map(function (Budget $budget) use ($month) {
                // Calculate spending for this category in the given month
                $spent = $budget->category->transactions()
                    ->where('user_id', $budget->user_id)
                    ->where('type', 'expense')
                    ->whereYear('date', substr($month, 0, 4))
                    ->whereMonth('date', substr($month, 5, 2))
                    ->sum('amount');

                return array_merge($budget->toArray(), [
                    'spent'      => (float) $spent,
                    'remaining'  => max(0, (float) $budget->amount - (float) $spent),
                    'percentage' => $budget->amount > 0
                        ? round(((float) $spent / (float) $budget->amount) * 100, 1)
                        : 0,
                ]);
            });

        return response()->json($budgets);
    }

    /**
     * Create or update a budget for the authenticated user.
     *
     * @param  StoreBudgetRequest $request the incoming HTTP request
     * @return JsonResponse
     */
    public function store(StoreBudgetRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Ensure category belongs to user
        $request->user()->categories()->findOrFail($data['category_id']);

        $budget = $request->user()->budgets()->updateOrCreate(
            ['category_id' => $data['category_id'], 'month' => $data['month']],
            ['amount' => $data['amount']]
        );

        return response()->json($budget->load('category'), 201);
    }

    /**
     * Delete a budget.
     *
     * @param  Request $request the incoming HTTP request
     * @param  Budget  $budget  the budget to delete
     * @return JsonResponse
     */
    public function destroy(Request $request, Budget $budget): JsonResponse
    {
        abort_if($budget->user_id !== $request->user()->id, 403);
        $budget->delete();

        return response()->json(['message' => 'Budget deleted']);
    }
}
