<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Client;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class VerifyClerkToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        try {
            // Retrieve JWKS from Clerk
            // Clerk provides a JWKS endpoint: https://api.clerk.com/v1/jwks
            $jwksUrl = 'https://api.clerk.com/v1/jwks';
            
            $keys = Cache::remember('clerk_jwks', 86400, function () use ($jwksUrl) {
                $client = new Client();
                $response = $client->get($jwksUrl, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . env('CLERK_SECRET_KEY')
                    ]
                ]);
                return json_decode($response->getBody()->getContents(), true);
            });

            // Parse keys
            $jwks = \Firebase\JWT\JWK::parseKeySet($keys);
            
            // Decode and verify token
            $decoded = JWT::decode($token, $jwks);
            
            // Sync user to database
            $clerkId = $decoded->sub;
            
            $user = User::firstOrCreate(
                ['clerk_id' => $clerkId],
                [
                    'name' => 'Clerk User', 
                    'email' => null, 
                    'password' => null
                ]
            );

            // Log the user in for this request
            Auth::login($user);

            return $next($request);

        } catch (\Exception $e) {
            Log::error('Clerk Token Verification Failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Unauthenticated or invalid token.'], 401);
        }
    }
}
