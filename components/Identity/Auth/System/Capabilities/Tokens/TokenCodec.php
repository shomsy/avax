<?php
declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Tokens;

/**
 * TokenCodec - Handles encoding and decoding of tokens (e.g. JWT).
 * 1:1 alignment with refactor.md.
 */
final readonly class TokenCodec
{
    public function encode(array $payload, string $secret) : string
    {
        return base64_encode(json_encode($payload)); // Simple placeholder
    }

    public function decode(string $token, string $secret) : array
    {
        return json_decode(base64_decode($token), true); // Simple placeholder
    }
}
