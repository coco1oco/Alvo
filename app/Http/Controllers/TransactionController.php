<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionController extends AbstractController
{
    /**
     * List all transactions for the authenticated user with optional filters.
     *
     * @param  Request $request the incoming HTTP request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()
            ->transactions()
            ->with(['account', 'toAccount', 'category'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc');

        // Filter by type (income, expense, transfer, or credit_card)
        if ($request->filled('type')) {
            if ($request->type === 'credit_card') {
                $query->where(function ($q) {
                    $q->whereHas('account', fn($a) => $a->where('type', 'credit_card'))
                      ->orWhereHas('toAccount', fn($a) => $a->where('type', 'credit_card'));
                });
            } elseif (in_array($request->type, ['income', 'expense', 'transfer'])) {
                $query->where('type', $request->type);
            }
        }

        // Filter by account
        if ($request->filled('account_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('account_id', $request->account_id)
                  ->orWhere('to_account_id', $request->account_id);
            });
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by date range
        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->to);
        }

        // Search description
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $transactions = $query->paginate(20);

        return response()->json($transactions);
    }

    /**
     * Create a new transaction and adjust account balances accordingly.
     *
     * @param  StoreTransactionRequest $request the incoming HTTP request
     * @param  TransactionService      $service the transaction service
     * @return JsonResponse
     */
    public function store(StoreTransactionRequest $request, TransactionService $service): JsonResponse
    {
        $data = $request->validated();
        $transaction = $service->createTransaction($request->user(), $data);

        return response()->json($transaction->load(['account', 'toAccount', 'category']), 201);
    }

    /**
     * Update an existing transaction and reapply balance effects.
     *
     * @param  UpdateTransactionRequest $request     the incoming HTTP request
     * @param  Transaction              $transaction the transaction to update
     * @param  TransactionService       $service     the transaction service
     * @return JsonResponse
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction, TransactionService $service): JsonResponse
    {
        abort_if($transaction->user_id !== $request->user()->id, 403);

        $data = $request->validated();
        $updatedTransaction = $service->updateTransaction($request->user(), $transaction, $data);

        return response()->json($updatedTransaction->load(['account', 'toAccount', 'category']));
    }

    /**
     * Delete a transaction and reverse its balance effect.
     *
     * @param  Request            $request     the incoming HTTP request
     * @param  Transaction        $transaction the transaction to delete
     * @param  TransactionService $service     the transaction service
     * @return JsonResponse
     */
    public function destroy(Request $request, Transaction $transaction, TransactionService $service): JsonResponse
    {
        abort_if($transaction->user_id !== $request->user()->id, 403);

        $service->deleteTransaction($request->user(), $transaction);

        return response()->json(['message' => 'Transaction deleted']);
    }

    /**
     * List soft-deleted transactions for the authenticated user.
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function trashed(Request $request): JsonResponse
    {
        $transactions = $request->user()
            ->transactions()
            ->onlyTrashed()
            ->with(['account', 'toAccount', 'category'])
            ->orderBy('deleted_at', 'desc')
            ->get();

        return response()->json($transactions);
    }

    /**
     * Restore a soft-deleted transaction and reapply its balance effect.
     *
     * @param  Request            $request
     * @param  int                $id
     * @param  TransactionService $service
     * @return JsonResponse
     */
    public function restore(Request $request, $id, TransactionService $service): JsonResponse
    {
        $transaction = $request->user()->transactions()->onlyTrashed()->findOrFail($id);
        
        $service->restoreTransaction($request->user(), $transaction);

        return response()->json(['message' => 'Transaction restored']);
    }

    /**
     * Permanently delete a soft-deleted transaction.
     *
     * @param  Request $request
     * @param  int     $id
     * @return JsonResponse
     */
    public function forceDelete(Request $request, $id): JsonResponse
    {
        $transaction = $request->user()->transactions()->onlyTrashed()->findOrFail($id);
        $transaction->forceDelete();

        return response()->json(['message' => 'Transaction permanently deleted']);
    }

    /**
     * Export all transactions for the authenticated user as a CSV file.
     *
     * @param  Request $request the incoming HTTP request
     * @return StreamedResponse
     */
    public function export(Request $request): StreamedResponse
    {
        $transactions = $request->user()
            ->transactions()
            ->with(['account', 'toAccount', 'category'])
            ->orderBy('date', 'desc')
            ->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="transactions.csv"',
        ];

        return response()->stream(function () use ($transactions) {
            $handle = fopen('php://output', 'w');

            // CSV Header row
            fputcsv($handle, ['Date', 'Type', 'Account', 'To Account', 'Category', 'Amount', 'Description']);

            foreach ($transactions as $txn) {
                fputcsv($handle, [
                    $txn->date->format('Y-m-d'),
                    $txn->type,
                    $txn->account->name ?? '',
                    $txn->toAccount->name ?? '',
                    $txn->category->name ?? '',
                    number_format((float) $txn->amount, 2, '.', ''),
                    $txn->description ?? '',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

}
