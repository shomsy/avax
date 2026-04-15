<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Auth\System\Capability\Identity\Jwt;

use Avax\Auth\System\Capability\Identity\Jwt\JwtDecoder;
use PHPUnit\Framework\TestCase;

final class JwtDecoderTest extends TestCase
{
    private JwtDecoder $decoder;
    private string $secret = 'test-secret-key-for-testing';

    protected function setUp(): void
    {
        $this->decoder = new JwtDecoder(
            secret: $this->secret,
            algorithm: 'HS256'
        );
    }

    public function test_extract_bearer_token_returns_token(): void
    {
        $authHeader = 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9';

        $result = $this->decoder->extractBearerToken($authHeader);

        $this->assertSame('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9', $result);
    }

    public function test_extract_bearer_token_returns_null_without_bearer(): void
    {
        $authHeader = 'Basic dXNlcjpwYXNz';

        $result = $this->decoder->extractBearerToken($authHeader);

        $this->assertNull($result);
    }

    public function test_extract_bearer_token_returns_null_for_empty_string(): void
    {
        $result = $this->decoder->extractBearerToken('');

        $this->assertNull($result);
    }

    public function test_generate_token_creates_valid_jwt(): void
    {
        $payload = ['user_id' => 123, 'email' => 'test@example.com'];

        $token = $this->decoder->generateToken($payload);

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
        $this->assertStringContainsString('.', $token);
    }

    public function test_decode_returns_decoded_payload(): void
    {
        $payload = ['user_id' => 456, 'name' => 'John'];
        $token = $this->decoder->generateToken($payload);

        $decoded = $this->decoder->decode($token);

        $this->assertNotNull($decoded);
        $this->assertObjectHasProperty('user_id', $decoded);
        $this->assertObjectHasProperty('name', $decoded);
        $this->assertObjectHasProperty('iat', $decoded);
        $this->assertObjectHasProperty('exp', $decoded);
        $this->assertSame(456, $decoded->user_id);
        $this->assertSame('John', $decoded->name);
    }

    public function test_decode_with_custom_expiration(): void
    {
        $payload = ['sub' => '123'];
        $token = $this->decoder->generateToken($payload, expiration: 7200);

        $decoded = $this->decoder->decode($token);

        $this->assertNotNull($decoded);
        $this->assertGreaterThan(time() + 7000, $decoded->exp);
    }
}
