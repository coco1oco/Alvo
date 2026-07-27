<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
     * @param  StoreAccountRequest $request the incoming HTTP request
     * @return JsonResponse
     */
    public function store(StoreAccountRequest $request): JsonResponse
    {
        $data = $request->validated();

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
     * @param  UpdateAccountRequest $request the incoming HTTP request
     * @param  Account              $account the account to update
     * @return JsonResponse
     */
    public function update(UpdateAccountRequest $request, Account $account): JsonResponse
    {
        $this->authorizeAccount($request, $account);

        $data = $request->validated();

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
