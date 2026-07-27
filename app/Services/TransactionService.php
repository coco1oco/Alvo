<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    /**
     * Create a new transaction and adjust account balances accordingly.
     *
     * @param  User  $user the authenticated user
     * @param  array $data the transaction data
     * @return Transaction
     */
    public function createTransaction(User $user, array $data): Transaction
    {
        $account = $user->accounts()->findOrFail($data['account_id']);

        return DB::transaction(function () use ($user, $data, $account) {
            $transaction = $user->transactions()->create($data);

            // Adjust balances
            match ($data['type']) {
                'income'   => $account->adjustBalance((float) $data['amount']),
                'expense'  => $account->adjustBalance(-(float) $data['amount']),
                'transfer' => (function () use ($data, $account, $user) {
                    $toAccount = $user->accounts()->findOrFail($data['to_account_id']);
                    $account->adjustBalance(-(float) $data['amount']);
                    $toAccount->adjustBalance((float) $data['amount']);
                })(),
            };

            return $transaction;
        });
    }

    /**
     * Update an existing transaction and reapply balance effects.
     *
     * @param  User        $user        the authenticated user
     * @param  Transaction $transaction the transaction to update
     * @param  array       $data        the transaction data to update
     * @return Transaction
     */
    public function updateTransaction(User $user, Transaction $transaction, array $data): Transaction
    {
        return DB::transaction(function () use ($user, $transaction, $data) {
            // Reverse old balance effect
            $this->reverseBalanceEffect($user, $transaction);

            $transaction->update($data);
            $transaction->refresh();

            // Apply new balance effect
            $this->applyBalanceEffect($user, $transaction);

            return $transaction;
        });
    }

    /**
     * Delete a transaction and reverse its balance effect.
     *
     * @param  User        $user        the authenticated user
     * @param  Transaction $transaction the transaction to delete
     * @return void
     */
    public function deleteTransaction(User $user, Transaction $transaction): void
    {
        DB::transaction(function () use ($user, $transaction) {
            $this->reverseBalanceEffect($user, $transaction);
            $transaction->delete();
        });
    }

    /**
     * Restore a soft-deleted transaction and reapply its balance effect.
     *
     * @param  User        $user        the authenticated user
     * @param  Transaction $transaction the soft-deleted transaction
     * @return void
     */
    public function restoreTransaction(User $user, Transaction $transaction): void
    {
        DB::transaction(function () use ($user, $transaction) {
            $transaction->restore();
            $this->applyBalanceEffect($user, $transaction);
        });
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
