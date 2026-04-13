<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Token;

/**
 * Issues tokens with one active key and verifies them against a rollover key ring.
 */
final readonly class MultiKeyHmacTokenCodec implements TokenCodecInterface
{
    /**
     * @param list<TokenCodecInterface> $verificationCodecs
     */
    public function __construct(
        private TokenCodecInterface $primaryCodec,
        private array $verificationCodecs = []
    ) {}

    public function encode(array $claims) : string
    {
        return $this->primaryCodec->encode($claims);
    }

    public function decode(string $token) : array|null
    {
        $claims = $this->primaryCodec->decode($token);

        if ($claims !== null) {
            return $claims;
        }

        foreach ($this->verificationCodecs as $codec) {
            $claims = $codec->decode($token);

            if ($claims !== null) {
                return $claims;
            }
        }

        return null;
    }
}
