<?php

namespace Tests\Feature;

use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VerifyClerkTokenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('clerk_jwks');
    }

    public function test_missing_bearer_token_returns_401(): void
    {
        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_valid_clerk_token_authenticates_and_provisions_user(): void
    {
        $config = [];
        $possibleConfs = [
            getenv('OPENSSL_CONF'),
            dirname(PHP_BINARY).'/extras/ssl/openssl.cnf',
            dirname(PHP_BINARY).'/openssl.cnf',
            'C:/xampp/php/extras/ssl/openssl.cnf',
        ];

        foreach ($possibleConfs as $path) {
            if ($path && file_exists($path)) {
                $config['config'] = $path;
                break;
            }
        }

        $res = openssl_pkey_new(array_merge([
            'digest_alg' => 'sha256',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ], $config));

        if (! $res) {
            $this->fail('Failed to generate OpenSSL private key for test.');
        }

        openssl_pkey_export($res, $privateKey, null, $config);
        $details = openssl_pkey_get_details($res);

        $n = rtrim(strtr(base64_encode($details['rsa']['n']), '+/', '-_'), '=');
        $e = rtrim(strtr(base64_encode($details['rsa']['e']), '+/', '-_'), '=');

        $jwks = [
            'keys' => [
                [
                    'kty' => 'RSA',
                    'alg' => 'RS256',
                    'use' => 'sig',
                    'kid' => 'test_key_1',
                    'n' => $n,
                    'e' => $e,
                ],
            ],
        ];

        // Fake the Clerk JWKS endpoint
        Http::fake([
            'https://api.clerk.com/v1/jwks*' => Http::response($jwks, 200),
        ]);

        // Create JWT payload with user details
        $payload = [
            'iss' => 'https://clerk.accounts.dev',
            'sub' => 'user_clerk_test_123',
            'email_address' => 'testuser@example.com',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'iat' => time(),
            'exp' => time() + 3600,
        ];

        $token = JWT::encode($payload, $privateKey, 'RS256', 'test_key_1');

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/dashboard');

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'clerk_id' => 'user_clerk_test_123',
            'name' => 'Jane Doe',
            'email' => 'testuser@example.com',
        ]);
    }

    public function test_invalid_token_returns_401(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer invalid.jwt.token')
            ->getJson('/api/dashboard');

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated or invalid token.']);
    }

    public function test_invalid_issuer_returns_401(): void
    {
        $config = [];
        $possibleConfs = [
            getenv('OPENSSL_CONF'),
            dirname(PHP_BINARY).'/extras/ssl/openssl.cnf',
            dirname(PHP_BINARY).'/openssl.cnf',
            'C:/xampp/php/extras/ssl/openssl.cnf',
        ];

        foreach ($possibleConfs as $path) {
            if ($path && file_exists($path)) {
                $config['config'] = $path;
                break;
            }
        }

        $res = openssl_pkey_new(array_merge([
            'digest_alg' => 'sha256',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ], $config));

        openssl_pkey_export($res, $privateKey, null, $config);
        $details = openssl_pkey_get_details($res);
        $n = rtrim(strtr(base64_encode($details['rsa']['n']), '+/', '-_'), '=');
        $e = rtrim(strtr(base64_encode($details['rsa']['e']), '+/', '-_'), '=');

        $jwks = [
            'keys' => [
                [
                    'kty' => 'RSA',
                    'alg' => 'RS256',
                    'use' => 'sig',
                    'kid' => 'test_key_1',
                    'n' => $n,
                    'e' => $e,
                ],
            ],
        ];

        Http::fake([
            'https://api.clerk.com/v1/jwks*' => Http::response($jwks, 200),
        ]);

        $payload = [
            'iss' => 'https://malicious.com',
            'sub' => 'user_clerk_test_123',
            'iat' => time(),
            'exp' => time() + 3600,
        ];

        $token = JWT::encode($payload, $privateKey, 'RS256', 'test_key_1');

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/dashboard');

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated or invalid token.']);
    }
}
