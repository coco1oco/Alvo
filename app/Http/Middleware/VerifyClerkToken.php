<?php

namespace App\Http\Middleware;

use App\Models\Account;
use App\Models\Category;
use App\Models\User;
use Closure;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyClerkToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        try {
            $jwksUrl = config('services.clerk.jwks_url', 'https://api.clerk.com/v1/jwks');
            $secretKey = config('services.clerk.secret_key');

            $keys = Cache::remember('clerk_jwks', 86400, function () use ($jwksUrl, $secretKey) {
                $response = Http::withToken($secretKey)
                    ->timeout(10)
                    ->get($jwksUrl);

                if (! $response->successful()) {
                    throw new \RuntimeException('Failed to fetch JWKS from Clerk API.');
                }

                return $response->json();
            });

            // Parse keys & decode token
            $jwks = JWK::parseKeySet($keys);
            $decoded = JWT::decode($token, $jwks);

            // Validate Issuer (iss) and Authorized Party (azp) / Audience (aud)
            $issuer = $decoded->iss ?? '';
            if (! str_contains($issuer, 'clerk')) {
                throw new \RuntimeException('Invalid token issuer.');
            }

            // Sync user to database
            $clerkId = $decoded->sub;
            $email = $decoded->email ?? $decoded->email_address ?? $decoded->primary_email ?? null;
            $name = $decoded->name ?? trim(($decoded->first_name ?? '').' '.($decoded->last_name ?? '')) ?: 'Clerk User';

            $user = User::firstOrCreate(
                ['clerk_id' => $clerkId],
                [
                    'name' => $name,
                    'email' => $email,
                ]
            );

            // Seed defaults for new users
            if ($user->wasRecentlyCreated) {
                $defaultCategories = [
                    ['name' => 'Salary',       'type' => 'income',  'color' => '#22c55e', 'icon' => 'briefcase'],
                    ['name' => 'Freelance',    'type' => 'income',  'color' => '#10b981', 'icon' => 'laptop'],
                    ['name' => 'Investment',   'type' => 'income',  'color' => '#06b6d4', 'icon' => 'trending-up'],
                    ['name' => 'Gift',         'type' => 'income',  'color' => '#a78bfa', 'icon' => 'gift'],
                    ['name' => 'Other Income', 'type' => 'income',  'color' => '#64748b', 'icon' => 'plus-circle'],
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

                $defaultAccounts = [
                    ['name' => 'Cash Wallet',  'type' => 'cash', 'color' => '#22c55e', 'icon' => 'wallet',   'balance' => 0],
                    ['name' => 'Bank Account', 'type' => 'bank', 'color' => '#6366f1', 'icon' => 'landmark', 'balance' => 0],
                ];

                foreach ($defaultCategories as $cat) {
                    Category::create(array_merge($cat, ['user_id' => $user->id]));
                }
                foreach ($defaultAccounts as $acc) {
                    Account::create(array_merge($acc, ['user_id' => $user->id]));
                }
            }

            // If user existed but didn't have email updated, sync it if present
            if ($email && $user->email !== $email) {
                $user->update(['email' => $email]);
            }

            // Log the user in for this request
            Auth::login($user);

            return $next($request);

        } catch (\Exception $e) {
            Log::error('Clerk Token Verification Failed', ['error' => $e->getMessage()]);

            return response()->json(['message' => 'Unauthenticated or invalid token.'], 401);
        }
    }
}
