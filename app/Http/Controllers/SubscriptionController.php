<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubscriptionRequest;
use App\Http\Requests\UpdateSubscriptionRequest;
use App\Models\Subscription;
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
}
