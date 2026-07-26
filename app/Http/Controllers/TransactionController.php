<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        // Filter by type
        if ($request->filled('type') && in_array($request->type, ['income', 'expense', 'transfer'])) {
            $query->where('type', $request->type);
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
     * @param  Request $request the incoming HTTP request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'account_id'    => 'required|exists:accounts,id',
            'to_account_id' => 'nullable|exists:accounts,id|different:account_id',
            'category_id'   => 'nullable|exists:categories,id',
            'type'          => 'required|in:income,expense,transfer',
            'amount'        => 'required|numeric|min:0.01',
            'description'   => 'nullable|string|max:500',
            'date'          => 'required|date',
        ]);

        // Ensure accounts belong to user
        $account = $request->user()->accounts()->findOrFail($data['account_id']);

        return DB::transaction(function () use ($request, $data, $account) {
            $transaction = $request->user()->transactions()->create($data);

            // Adjust balances
            match ($data['type']) {
                'income'   => $account->adjustBalance((float) $data['amount']),
                'expense'  => $account->adjustBalance(-(float) $data['amount']),
                'transfer' => (function () use ($data, $account, $request) {
                    $toAccount = $request->user()->accounts()->findOrFail($data['to_account_id']);
                    $account->adjustBalance(-(float) $data['amount']);
                    $toAccount->adjustBalance((float) $data['amount']);
                })(),
            };

            return response()->json($transaction->load(['account', 'toAccount', 'category']), 201);
        });
    }

    /**
     * Update an existing transaction and reapply balance effects.
     *
     * @param  Request     $request     the incoming HTTP request
     * @param  Transaction $transaction the transaction to update
     * @return JsonResponse
     */
    public function update(Request $request, Transaction $transaction): JsonResponse
    {
        abort_if($transaction->user_id !== $request->user()->id, 403);

        $data = $request->validate([
            'account_id'    => 'sometimes|required|exists:accounts,id',
            'to_account_id' => 'nullable|exists:accounts,id',
            'category_id'   => 'nullable|exists:categories,id',
            'type'          => 'sometimes|required|in:income,expense,transfer',
            'amount'        => 'sometimes|required|numeric|min:0.01',
            'description'   => 'nullable|string|max:500',
            'date'          => 'sometimes|required|date',
        ]);

        return DB::transaction(function () use ($request, $transaction, $data) {
            // Reverse old balance effect
            $this->reverseBalanceEffect($request->user(), $transaction);

            $transaction->update($data);
            $transaction->refresh();

            // Apply new balance effect
            $this->applyBalanceEffect($request->user(), $transaction);

            return response()->json($transaction->load(['account', 'toAccount', 'category']));
        });
    }

    /**
     * Delete a transaction and reverse its balance effect.
     *
     * @param  Request     $request     the incoming HTTP request
     * @param  Transaction $transaction the transaction to delete
     * @return JsonResponse
     */
    public function destroy(Request $request, Transaction $transaction): JsonResponse
    {
        abort_if($transaction->user_id !== $request->user()->id, 403);

        DB::transaction(function () use ($request, $transaction) {
            $this->reverseBalanceEffect($request->user(), $transaction);
            $transaction->delete();
        });

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
     * @param  Request $request
     * @param  int     $id
     * @return JsonResponse
     */
    public function restore(Request $request, $id): JsonResponse
    {
        $transaction = $request->user()->transactions()->onlyTrashed()->findOrFail($id);

        DB::transaction(function () use ($request, $transaction) {
            $transaction->restore();
            $this->applyBalanceEffect($request->user(), $transaction);
        });

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

    /**
     * Reverse the balance effect of a transaction on the relevant accounts.
     *
     * @param  User        $user        the authenticated user
     * @param  Transaction $transaction the transaction whose effect should be reversed
     * @return void
     */
    private function reverseBalanceEffect(User $user, Transaction $transaction): void
    {
        $account = $user->accounts()->find($transaction->account_id);
        if (!$account) return;

        match ($transaction->type) {
            'income'   => $account->adjustBalance(-(float) $transaction->amount),
            'expense'  => $account->adjustBalance((float) $transaction->amount),
            'transfer' => (function () use ($user, $transaction, $account) {
                $toAccount = $user->accounts()->find($transaction->to_account_id);
                $account->adjustBalance((float) $transaction->amount);
                if ($toAccount) $toAccount->adjustBalance(-(float) $transaction->amount);
            })(),
        };
    }

    /**
     * Apply the balance effect of a transaction on the relevant accounts.
     *
     * @param  User        $user        the authenticated user
     * @param  Transaction $transaction the transaction to apply
     * @return void
     */
    private function applyBalanceEffect(User $user, Transaction $transaction): void
    {
        $account = $user->accounts()->find($transaction->account_id);
        if (!$account) return;

        match ($transaction->type) {
            'income'   => $account->adjustBalance((float) $transaction->amount),
            'expense'  => $account->adjustBalance(-(float) $transaction->amount),
            'transfer' => (function () use ($user, $transaction, $account) {
                $toAccount = $user->accounts()->find($transaction->to_account_id);
                $account->adjustBalance(-(float) $transaction->amount);
                if ($toAccount) $toAccount->adjustBalance((float) $transaction->amount);
            })(),
        };
    }
}
