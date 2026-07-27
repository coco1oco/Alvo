<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Category;
use App\Models\User;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\LoginUserRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends AbstractController
{
    /** @var array[] */
    private array $defaultCategories = [
        // Income categories
        ['name' => 'Salary',       'type' => 'income',  'color' => '#22c55e', 'icon' => 'briefcase'],
        ['name' => 'Freelance',    'type' => 'income',  'color' => '#10b981', 'icon' => 'laptop'],
        ['name' => 'Investment',   'type' => 'income',  'color' => '#06b6d4', 'icon' => 'trending-up'],
        ['name' => 'Gift',         'type' => 'income',  'color' => '#a78bfa', 'icon' => 'gift'],
        ['name' => 'Other Income', 'type' => 'income',  'color' => '#64748b', 'icon' => 'plus-circle'],
        // Expense categories
        ['name' => 'Food',          'type' => 'expense', 'color' => '#f97316', 'icon' => 'utensils'],
        ['name' => 'Rent',          'type' => 'expense', 'color' => '#ef4444', 'icon' => 'home'],
        ['name' => 'Transport',     'type' => 'expense', 'color' => '#3b82f6', 'icon' => 'car'],
        ['name' => 'Shopping',      'type' => 'expense', 'color' => '#ec4899', 'icon' => 'shopping-bag'],
        ['name' => 'Utilities',     'type' => 'expense', 'color' => '#f59e0b', 'icon' => 'zap'],
        ['name' => 'Healthcare',    'type' => 'expense', 'color' => '#14b8a6', 'icon' => 'heart'],
        ['name' => 'Entertainment', 'type' => 'expense', 'color' => '#8b5cf6', 'icon' => 'film'],
        ['name' => 'Education',     'type' => 'expense', 'color' => '#6366f1', 'icon' => 'book'],
        ['name' => 'Other',         'type' => 'expense', 'color' => '#64748b', 'icon' => 'more-horizontal'],
    ];

    /** @var array[] */
    private array $defaultAccounts = [
        ['name' => 'Cash Wallet',  'type' => 'cash', 'color' => '#22c55e', 'icon' => 'wallet',   'balance' => 0],
        ['name' => 'Bank Account', 'type' => 'bank', 'color' => '#6366f1', 'icon' => 'landmark', 'balance' => 0],
    ];

    /**
     * Register a new user and seed default categories and accounts.
     *
     * @param  RegisterUserRequest $request the incoming HTTP request
     * @return JsonResponse
     */
    public function register(RegisterUserRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Seed default categories
        foreach ($this->defaultCategories as $cat) {
            Category::create(array_merge($cat, ['user_id' => $user->id]));
        }

        // Seed default accounts
        foreach ($this->defaultAccounts as $acc) {
            Account::create(array_merge($acc, ['user_id' => $user->id]));
        }

        Auth::login($user);

        return response()->json([
            'user'    => $user,
            'message' => 'Registration successful',
        ], 201);
    }

    /**
     * Authenticate an existing user.
     *
     * @param  LoginUserRequest $request the incoming HTTP request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function login(LoginUserRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $request->session()->regenerate();

        return response()->json([
            'user'    => Auth::user(),
            'message' => 'Login successful',
        ]);
    }

    /**
     * Log the current user out and invalidate their session.
     *
     * @param  Request $request the incoming HTTP request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * Return the currently authenticated user.
     *
     * @param  Request $request the incoming HTTP request
     * @return JsonResponse
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}
