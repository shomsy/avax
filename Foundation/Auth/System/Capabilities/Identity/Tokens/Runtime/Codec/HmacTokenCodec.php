<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Codec;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use SensitiveParameter;
use Throwable;

/**
 * Encodes and decodes HMAC-signed tokens using the Firebase JWT library.
 */
final readonly class HmacTokenCodec implements TokenCodecInterface
{
    public function __construct(
        #[SensitiveParameter] private string $secret,
        private string                       $algorithm = 'HS256',
        private string|null                  $keyId = null
    ) {}

    /**
     * @param array<string, mixed> $claims
     */
    public function encode(array $claims) : string
    {
        return JWT::encode(payload: $claims, key: $this->secret, alg: $this->algorithm, keyId: $this->keyId);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function decode(#[SensitiveParameter] string $token) : array|null
    {
        try {
            return (array) JWT::decode(jwt: $token, keyOrKeyArray: new Key(keyMaterial: $this->secret, algorithm: $this->algorithm));
        } catch (Throwable) {
            return null;
        }
    }
}
