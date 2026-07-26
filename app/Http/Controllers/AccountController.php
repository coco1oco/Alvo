<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountController extends AbstractController
{
    /**
     * List all accounts belonging to the authenticated user.
     *
     * @param  Request $request the incoming HTTP request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $accounts = $request->user()
            ->accounts()
            ->orderBy('name')
            ->get();

        return response()->json($accounts);
    }

    /**
     * Create a new account for the authenticated user.
     *
     * @param  Request $request the incoming HTTP request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'    => [
                'required',
                'string',
                'max:100',
                Rule::unique('accounts')->where(function ($query) use ($request) {
                    return $query->where('user_id', $request->user()->id);
                })
            ],
            'type'    => 'required|in:cash,bank,credit_card,savings,other',
            'balance' => 'nullable|numeric',
            'color'   => 'nullable|string|max:7',
            'icon'    => 'nullable|string|max:50',
        ]);

        $account = $request->user()->accounts()->create([
            'name'    => $data['name'],
            'type'    => $data['type'],
            'balance' => $data['balance'] ?? 0,
            'color'   => $data['color'] ?? '#6366f1',
            'icon'    => $data['icon'] ?? 'wallet',
        ]);

        return response()->json($account, 201);
    }

    /**
     * Update an existing account.
     *
     * @param  Request $request the incoming HTTP request
     * @param  Account $account the account to update
     * @return JsonResponse
     */
    public function update(Request $request, Account $account): JsonResponse
    {
        $this->authorizeAccount($request, $account);

        $data = $request->validate([
            'name'  => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('accounts')->where(function ($query) use ($request) {
                    return $query->where('user_id', $request->user()->id);
                })->ignore($account->id)
            ],
            'type'  => 'sometimes|required|in:cash,bank,credit_card,savings,other',
            'color' => 'sometimes|nullable|string|max:7',
            'icon'  => 'sometimes|nullable|string|max:50',
            'is_archived' => 'sometimes|boolean',
        ]);

        $account->update($data);

        return response()->json($account);
    }

    /**
     * Delete an account.
     *
     * @param  Request $request the incoming HTTP request
     * @param  Account $account the account to delete
     * @return JsonResponse
     */
    public function destroy(Request $request, Account $account): JsonResponse
    {
        $this->authorizeAccount($request, $account);

        $account->delete();

        return response()->json(['message' => 'Account deleted']);
    }

    /**
     * Abort with 403 if the account does not belong to the authenticated user.
     *
     * @param  Request $request the incoming HTTP request
     * @param  Account $account the account to authorize against
     * @return void
     */
    private function authorizeAccount(Request $request, Account $account): void
    {
        abort_if($account->user_id !== $request->user()->id, 403, 'Unauthorized');
    }
}
