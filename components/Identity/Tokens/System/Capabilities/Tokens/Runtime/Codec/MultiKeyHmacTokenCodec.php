<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Codec;

use SensitiveParameter;

/**
 * Composite codec that uses a primary codec for encoding and multiple codecs for verification/decoding.
 */
final readonly class MultiKeyHmacTokenCodec implements TokenCodecInterface
{
    /**
     * @param list<TokenCodecInterface> $verificationCodecs
     */
    public function __construct(
        private TokenCodecInterface $tokenCodec,
        private array               $verificationCodecs = [],
    ) {}

    public function encode(array $claims) : string
    {
        return $this->tokenCodec->encode(claims: $claims);
    }

    public function decode(#[SensitiveParameter] string $token) : array|null
    {
        $decoded = $this->tokenCodec->decode(token: $token);

        if ($decoded !== null) {
            return $decoded;
        }

        foreach ($this->verificationCodecs as $verificationCodec) {
            $decoded = $verificationCodec->decode(token: $token);

            if ($decoded !== null) {
                return $decoded;
            }
        }

        return null;
    }
}
