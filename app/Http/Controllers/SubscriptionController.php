<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubscriptionRequest;
use App\Http\Requests\UpdateSubscriptionRequest;
use App\Models\Subscription;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends AbstractController
{
    /**
     * List all subscriptions for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $subscriptions = $request->user()
            ->subscriptions()
            ->with(['account', 'category'])
            ->orderBy('next_renewal_date', 'asc')
            ->get();

        return response()->json($subscriptions);
    }

    /**
     * Create a new subscription record.
     */
    public function store(StoreSubscriptionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $subscription = $request->user()->subscriptions()->create($data);

        return response()->json($subscription->load(['account', 'category']), 201);
    }

    /**
     * Update an existing subscription.
     */
    public function update(UpdateSubscriptionRequest $request, Subscription $subscription): JsonResponse
    {
        abort_if($subscription->user_id !== $request->user()->id, 403);

        $data = $request->validated();
        $subscription->update($data);

        return response()->json($subscription->fresh(['account', 'category']));
    }

    /**
     * Delete a subscription.
     */
    public function destroy(Request $request, Subscription $subscription): JsonResponse
    {
        abort_if($subscription->user_id !== $request->user()->id, 403);

        $subscription->delete();

        return response()->json(['message' => 'Subscription deleted successfully']);
    }

    /**
     * Process/log a subscription payment now (creates a real transaction record and advances next_renewal_date).
     */
    public function process(Request $request, Subscription $subscription, TransactionService $service): JsonResponse
    {
        abort_if($subscription->user_id !== $request->user()->id, 403);

        // Find or fallback account
        $accountId = $subscription->account_id ?? $request->user()->accounts()->first()?->id;
        if (! $accountId) {
            return response()->json(['message' => 'Please select a valid account for this payment.'], 422);
        }

        // Create actual expense transaction
        $txnData = [
            'account_id' => $accountId,
            'category_id' => $subscription->category_id,
            'type' => 'expense',
            'amount' => $subscription->amount,
            'description' => $subscription->name.' Payment',
            'date' => now()->toDateString(),
        ];

        $transaction = $service->createTransaction($request->user(), $txnData);

        // Advance next renewal date to next cycle
        $currentRenewal = Carbon::parse($subscription->next_renewal_date ?? now());
        $nextRenewal = match ($subscription->billing_cycle) {
            'weekly' => $currentRenewal->copy()->addWeek(),
            'yearly' => $currentRenewal->copy()->addYear(),
            default => $currentRenewal->copy()->addMonth(),
        };

        // Advance further if nextRenewal is still in past
        while ($nextRenewal->isPast() && ! $nextRenewal->isToday()) {
            match ($subscription->billing_cycle) {
                'weekly' => $nextRenewal->addWeek(),
                'yearly' => $nextRenewal->addYear(),
                default => $nextRenewal->addMonth(),
            };
        }

        $subscription->update(['next_renewal_date' => $nextRenewal->toDateString()]);

        return response()->json([
            'message' => 'Subscription payment logged successfully',
            'transaction' => $transaction->load(['account', 'category']),
            'subscription' => $subscription->fresh(['account', 'category']),
        ]);
    }
}
