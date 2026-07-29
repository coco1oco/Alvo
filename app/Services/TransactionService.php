<?php

namespace App\Services;

use App\Models\Account;
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

        // Block transfers FROM a credit card — cards cannot be a funding source.
        if ($data['type'] === 'transfer' && $account->type === 'credit_card') {
            abort(422, 'Transfers cannot originate from a credit card account. Use "Pay Bill" to pay down a card.');
        }

        return DB::transaction(function () use ($user, $data, $account) {
            $transaction = $user->transactions()->create($data);

            match ($data['type']) {
                'income'   => $this->adjustBalance($account, 'income', (float) $data['amount']),
                'expense'  => $this->adjustBalance($account, 'expense', (float) $data['amount']),
                'transfer' => (function () use ($data, $account, $user) {
                    $toAccount = $user->accounts()->findOrFail($data['to_account_id']);
                    // Deduct from source (always a non-CC account at this point)
                    $this->adjustBalance($account, 'expense', (float) $data['amount']);
                    // Credit card receiving a transfer = "Pay Bill" = debt reduction
                    // Regular account receiving = deposit
                    if ($toAccount->type === 'credit_card') {
                        $toAccount->adjustBalance(-(float) $data['amount']); // debt decreases
                    } else {
                        $toAccount->adjustBalance((float) $data['amount']);  // balance increases
                    }
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
            'income'   => $this->adjustBalance($account, 'income', -(float) $transaction->amount),
            'expense'  => $this->adjustBalance($account, 'expense', -(float) $transaction->amount),
            'transfer' => (function () use ($user, $transaction, $account) {
                // Restore source account
                $this->adjustBalance($account, 'expense', -(float) $transaction->amount);
                $toAccount = $user->accounts()->find($transaction->to_account_id);
                if ($toAccount) {
                    if ($toAccount->type === 'credit_card') {
                        $toAccount->adjustBalance((float) $transaction->amount); // re-add debt
                    } else {
                        $toAccount->adjustBalance(-(float) $transaction->amount); // remove deposit
                    }
                }
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
            'income'   => $this->adjustBalance($account, 'income', (float) $transaction->amount),
            'expense'  => $this->adjustBalance($account, 'expense', (float) $transaction->amount),
            'transfer' => (function () use ($user, $transaction, $account) {
                $this->adjustBalance($account, 'expense', (float) $transaction->amount);
                $toAccount = $user->accounts()->find($transaction->to_account_id);
                if ($toAccount) {
                    if ($toAccount->type === 'credit_card') {
                        $toAccount->adjustBalance(-(float) $transaction->amount); // pay down debt
                    } else {
                        $toAccount->adjustBalance((float) $transaction->amount);
                    }
                }
            })(),
        };
    }

    /**
     * Apply a signed balance adjustment to an account, respecting credit card polarity.
     *
     * Credit card balance = outstanding debt (stored as positive).
     *   income  on CC → debt DECREASES (refund/cashback posted to card)
     *   expense on CC → debt INCREASES  (charge to the card)
     *
     * Regular account balance = funds available (stored as positive).
     *   income  → balance INCREASES
     *   expense → balance DECREASES
     *
     * Pass a negative $amount to reverse/undo an effect.
     *
     * @param  Account $account the account to adjust
     * @param  string  $type    'income' or 'expense'
     * @param  float   $amount  the raw amount (positive = apply, negative = reverse)
     * @return void
     */
    private function adjustBalance(Account $account, string $type, float $amount): void
    {
        if ($account->type === 'credit_card') {
            // For credit cards the sign is inverted relative to normal accounts.
            // expense → debt goes UP (+amount), income → debt goes DOWN (-amount)
            $delta = ($type === 'expense') ? $amount : -$amount;
        } else {
            // For regular accounts: income → balance UP, expense → balance DOWN
            $delta = ($type === 'income') ? $amount : -$amount;
        }

        $account->adjustBalance($delta);
    }
}
