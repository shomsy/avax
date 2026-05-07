<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\System\Capabilities\Tokens;

use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Codec\HmacTokenCodec;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Codec\TokenCodecInterface;
use InvalidArgumentException;
use SensitiveParameter;

/**
 * TokenCodec - Handles encoding and decoding of tokens (e.g. JWT).
 * 1:1 alignment with refactor.md.
 */
final readonly class TokenCodec
{
    private TokenCodecInterface $tokenCodec;

    public function __construct(
        #[SensitiveParameter]
        string $secret,
        string $algorithm = 'HS256',
    )
    {
        $this->tokenCodec = new HmacTokenCodec(secret: $secret, algorithm: $algorithm);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function encode(array $payload) : string
    {
        return $this->tokenCodec->encode(claims: $payload);
    }

    /**
     * @return array<string, mixed>
     */
    public function decode(string $token) : array
    {
        $payload = $this->tokenCodec->decode(token: $token);

        if ($payload === null) {
            throw new InvalidArgumentException(message: 'Token signature, algorithm, or payload is invalid.');
        }

        return $payload;
    }
}
