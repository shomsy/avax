<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Identity\Jwt;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use RuntimeException;
use SensitiveParameter;

/**
 * Decodes JWT tokens from HTTP requests.
 *
 * Banal: Extracts and decodes Bearer tokens from Authorization headers.
 */
final readonly class JwtDecoder
{
    private const string BEARER_PREFIX = 'Bearer ';

    public function __construct(
        private string $secret,
        private string $algorithm = 'HS256',
    ) {}

    public function extractBearerToken(string $authHeader): ?string
    {
        if (str_starts_with($authHeader, self::BEARER_PREFIX)) {
            return substr($authHeader, strlen(self::BEARER_PREFIX));
        }

        return null;
    }

    public function decode(#[SensitiveParameter] string $token): object|null
    {
        return JWT::decode(
            jwt: $token,
            keyOrKeyArray: $this->createKey(),
        );
    }

    public function generateToken(array $payload, int $expiration = 3600): string
    {
        $issuedAt = time();
        $payload['iat'] = $issuedAt;
        $payload['exp'] = $issuedAt + $expiration;

        return JWT::encode(
            payload: $payload,
            key: $this->secret,
            alg: $this->algorithm,
        );
    }

    private function createKey(): Key
    {
        return new Key(
            keyMaterial: $this->secret,
            algorithm: $this->algorithm,
        );
    }
}
