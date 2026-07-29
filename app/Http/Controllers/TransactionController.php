<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

        // Filter by reimbursable
        if ($request->has('reimbursable') && $request->reimbursable !== '') {
            $query->where('is_reimbursable', filter_var($request->reimbursable, FILTER_VALIDATE_BOOLEAN));
        }

        // Filter by split
        if ($request->has('is_split') && $request->is_split !== '') {
            $query->where('is_split', filter_var($request->is_split, FILTER_VALIDATE_BOOLEAN));
        }

        // Search description & notes
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', '%' . $search . '%')
                  ->orWhere('notes', 'like', '%' . $search . '%');
            });
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
        if (empty($data['is_split'])) {
            $data['split_data'] = null;
        }

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('attachments', 'public');
            $data['attachment_path'] = '/storage/' . $path;
        }

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
        if (empty($data['is_split'])) {
            $data['split_data'] = null;
        }

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('attachments', 'public');
            $data['attachment_path'] = '/storage/' . $path;
        }

        $updatedTransaction = $service->updateTransaction($request->user(), $transaction, $data);

        return response()->json($updatedTransaction->load(['account', 'toAccount', 'category']));
    }

    /**
     * Serve attachment file for a transaction.
     */
    public function showAttachment(Request $request, Transaction $transaction)
    {
        abort_if($transaction->user_id !== $request->user()->id, 403);
        abort_if(empty($transaction->attachment_path), 404, 'No attachment found.');

        $relativePath = str_replace('/storage/', '', $transaction->attachment_path);
        if (! Storage::disk('public')->exists($relativePath)) {
            abort(404, 'Attachment file not found on disk.');
        }

        return Storage::disk('public')->response($relativePath);
    }

    /**
     * Toggle individual participant settlement status in a split transaction.
     */
    public function toggleSplitSettlement(Request $request, Transaction $transaction, int $index): JsonResponse
    {
        abort_if($transaction->user_id !== $request->user()->id, 403);
        abort_if(! $transaction->is_split || ! is_array($transaction->split_data), 400, 'Transaction is not a split expense.');

        $splitData = $transaction->split_data;
        $participants = $splitData['participants'] ?? [];

        if (isset($participants[$index])) {
            $current = ! empty($participants[$index]['is_settled']);
            $participants[$index]['is_settled'] = ! $current;
            $participants[$index]['settled_at'] = ! $current ? now()->toIso8601String() : null;
            $splitData['participants'] = $participants;

            $transaction->split_data = $splitData;
            $transaction->save();
        }

        return response()->json($transaction->load(['account', 'toAccount', 'category']));
    }

    /**
     * Get summary of all split expenses and debt tracking per person.
     */
    public function splitsSummary(Request $request): JsonResponse
    {
        $transactions = $request->user()
            ->transactions()
            ->where('is_split', true)
            ->with(['account', 'category'])
            ->orderBy('date', 'desc')
            ->get();

        $peopleMap = [];
        $totalOwedToUser = 0.0;
        $totalSettled = 0.0;

        foreach ($transactions as $txn) {
            $participants = data_get($txn->split_data, 'participants', []);
            foreach ($participants as $idx => $p) {
                $name = trim((string) ($p['name'] ?? ''));
                if (! $name) continue;

                $amount = (float) ($p['amount'] ?? 0);
                $isSettled = ! empty($p['is_settled']);

                if (! isset($peopleMap[$name])) {
                    $peopleMap[$name] = [
                        'name' => $name,
                        'total_owed' => 0.0,
                        'total_settled' => 0.0,
                        'pending_count' => 0,
                        'settled_count' => 0,
                        'splits' => [],
                    ];
                }

                if ($isSettled) {
                    $peopleMap[$name]['total_settled'] += $amount;
                    $peopleMap[$name]['settled_count']++;
                    $totalSettled += $amount;
                } else {
                    $peopleMap[$name]['total_owed'] += $amount;
                    $peopleMap[$name]['pending_count']++;
                    $totalOwedToUser += $amount;
                }

                $peopleMap[$name]['splits'][] = [
                    'transaction_id' => $txn->id,
                    'participant_index' => $idx,
                    'description' => $txn->description ?: 'Expense',
                    'date' => $txn->date->format('Y-m-d'),
                    'total_amount' => (float) $txn->amount,
                    'person_share' => $amount,
                    'is_settled' => $isSettled,
                    'settled_at' => $p['settled_at'] ?? null,
                    'category' => $txn->category->name ?? null,
                    'account' => $txn->account->name ?? null,
                ];
            }
        }

        $people = array_values($peopleMap);

        return response()->json([
            'summary' => [
                'total_owed' => $totalOwedToUser,
                'total_settled' => $totalSettled,
                'total_people' => count($people),
                'pending_splits_count' => collect($people)->sum('pending_count'),
            ],
            'people' => $people,
            'transactions' => $transactions,
        ]);
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
            fputcsv($handle, ['Date', 'Type', 'Account', 'To Account', 'Category', 'Amount', 'Description', 'Notes', 'Reimbursable', 'Split', 'Split Participants']);

            foreach ($transactions as $txn) {
                $participants = collect(data_get($txn->split_data, 'participants', []))
                    ->map(function ($participant) {
                        $name = trim((string) ($participant['name'] ?? ''));
                        $amount = number_format((float) ($participant['amount'] ?? 0), 2, '.', '');
                        $settled = ! empty($participant['is_settled']) ? ' [Settled]' : '';

                        return $name === '' ? null : $name . ' (' . $amount . ')' . $settled;
                    })
                    ->filter()
                    ->values()
                    ->implode(' | ');

                fputcsv($handle, [
                    $txn->date->format('Y-m-d'),
                    $txn->type,
                    $txn->account->name ?? '',
                    $txn->toAccount->name ?? '',
                    $txn->category->name ?? '',
                    number_format((float) $txn->amount, 2, '.', ''),
                    $txn->description ?? '',
                    $txn->notes ?? '',
                    $txn->is_reimbursable ? 'Yes' : 'No',
                    $txn->is_split ? 'Yes' : 'No',
                    $participants,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

}
