<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecurringTransactionRequest;
use App\Http\Requests\UpdateRecurringTransactionRequest;
use App\Models\RecurringTransaction;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecurringTransactionController extends AbstractController
{
    /**
     * List all recurring transactions for the authenticated user.
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $items = $request->user()
            ->recurringTransactions()
            ->with(['account', 'toAccount', 'category'])
            ->orderBy('next_due_date', 'asc')
            ->get();

        return response()->json($items);
    }

    /**
     * Store a new recurring transaction schedule.
     *
     * @param  StoreRecurringTransactionRequest $request
     * @return JsonResponse
     */
    public function store(StoreRecurringTransactionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $startDate = Carbon::parse($data['start_date']);

        if (empty($data['next_due_date'])) {
            $data['next_due_date'] = $startDate->isPast()
                ? $this->calculateNextDueDate($startDate, $data['frequency'])
                : $startDate->toDateString();
        }

        $recurring = $request->user()->recurringTransactions()->create($data);

        return response()->json($recurring->load(['account', 'toAccount', 'category']), 201);
    }

    /**
     * Update an existing recurring transaction schedule.
     *
     * @param  UpdateRecurringTransactionRequest $request
     * @param  RecurringTransaction             $recurringTransaction
     * @return JsonResponse
     */
    public function update(UpdateRecurringTransactionRequest $request, RecurringTransaction $recurringTransaction): JsonResponse
    {
        abort_if($recurringTransaction->user_id !== $request->user()->id, 403);

        $data = $request->validated();
        $recurringTransaction->update($data);

        return response()->json($recurringTransaction->fresh(['account', 'toAccount', 'category']));
    }

    /**
     * Delete a recurring transaction schedule.
     *
     * @param  Request              $request
     * @param  RecurringTransaction $recurringTransaction
     * @return JsonResponse
     */
    public function destroy(Request $request, RecurringTransaction $recurringTransaction): JsonResponse
    {
        abort_if($recurringTransaction->user_id !== $request->user()->id, 403);

        $recurringTransaction->delete();

        return response()->json(['message' => 'Recurring transaction deleted']);
    }

    /**
     * Process/log a recurring transaction now (creates a real transaction record and advances next_due_date).
     *
     * @param  Request              $request
     * @param  RecurringTransaction $recurringTransaction
     * @param  TransactionService   $service
     * @return JsonResponse
     */
    public function process(Request $request, RecurringTransaction $recurringTransaction, TransactionService $service): JsonResponse
    {
        abort_if($recurringTransaction->user_id !== $request->user()->id, 403);

        // Create actual transaction
        $txnData = [
            'account_id'    => $recurringTransaction->account_id,
            'to_account_id' => $recurringTransaction->to_account_id,
            'category_id'   => $recurringTransaction->category_id,
            'type'          => $recurringTransaction->type,
            'amount'        => $recurringTransaction->amount,
            'description'   => $recurringTransaction->description ? $recurringTransaction->description . ' (Recurring)' : 'Recurring Transaction',
            'date'          => now()->toDateString(),
        ];

        $transaction = $service->createTransaction($request->user(), $txnData);

        // Advance next due date to next period
        $currentDue = Carbon::parse($recurringTransaction->next_due_date);
        $nextDue = $this->calculateNextDueDate($currentDue, $recurringTransaction->frequency);
        $recurringTransaction->update(['next_due_date' => $nextDue]);

        return response()->json([
            'message' => 'Transaction logged successfully',
            'transaction' => $transaction->load(['account', 'toAccount', 'category']),
            'recurring' => $recurringTransaction->fresh(['account', 'toAccount', 'category']),
        ]);
    }

    /**
     * Calculate the next due date given a base date and frequency string.
     *
     * @param  Carbon $date
     * @param  string $frequency
     * @return string (Y-m-d)
     */
    private function calculateNextDueDate(Carbon $date, string $frequency): string
    {
        $next = $date->copy();
        match ($frequency) {
            'daily'     => $next->addDay(),
            'weekly'    => $next->addWeek(),
            'bi-weekly' => $next->addWeeks(2),
            'monthly'   => $next->addMonth(),
            'quarterly' => $next->addMonths(3),
            'yearly'    => $next->addYear(),
            default     => $next->addMonth(),
        };

        // If calculated date is still in the past, keep advancing until future
        while ($next->isPast() && !$next->isToday()) {
            match ($frequency) {
                'daily'     => $next->addDay(),
                'weekly'    => $next->addWeek(),
                'bi-weekly' => $next->addWeeks(2),
                'monthly'   => $next->addMonth(),
                'quarterly' => $next->addMonths(3),
                'yearly'    => $next->addYear(),
                default     => $next->addMonth(),
            };
        }

        return $next->toDateString();
    }
}
