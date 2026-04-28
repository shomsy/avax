<?php

declare(strict_types=1);

namespace Avax\Components\Auth\System\Capabilities\Identity\Tokens\Runtime\Codec;

use SensitiveParameter;

/**
 * Composite codec that uses a primary codec for encoding and multiple codecs for verification/decoding.
 */
final readonly class MultiKeyHmacTokenCodec implements TokenCodecInterface
{
    /**
     * @param TokenCodecInterface       $primaryCodec
     * @param list<TokenCodecInterface> $verificationCodecs
     */
    public function __construct(
        private TokenCodecInterface $primaryCodec,
        private array               $verificationCodecs = []
    ) {}

    public function encode(array $claims) : string
    {
        return $this->primaryCodec->encode(claims: $claims);
    }

    public function decode(#[SensitiveParameter] string $token) : array|null
    {
        $decoded = $this->primaryCodec->decode(token: $token);

        if ($decoded !== null) {
            return $decoded;
        }

        foreach ($this->verificationCodecs as $codec) {
            $decoded = $codec->decode(token: $token);

            if ($decoded !== null) {
                return $decoded;
            }
        }

        return null;
    }
}
