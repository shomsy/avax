<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Token;

use SensitiveParameter;

/**
 * Issues tokens with one active key and verifies them against a rollover key ring.
 */
final readonly class MultiKeyHmacTokenCodec implements TokenCodecInterface
{
    /** @var list<TokenCodecInterface> */
    private array               $verificationCodecs;
    private TokenCodecInterface $primaryCodec;

    /**
     * @param list<TokenCodecInterface> $verificationCodecs
     */
    public function __construct(
        TokenCodecInterface $primaryCodec,
        array               $verificationCodecs = []
    )
    {
        $this->primaryCodec       = $primaryCodec;
        $this->verificationCodecs = $verificationCodecs;
    }

    public function encode(array $claims) : string
    {
        return $this->primaryCodec->encode(claims: $claims);
    }

    public function decode(#[SensitiveParameter] string $token) : array|null
    {
        $claims = $this->primaryCodec->decode(token: $token);

        if ($claims !== null) {
            return $claims;
        }

        foreach ($this->verificationCodecs as $codec) {
            $claims = $codec->decode(token: $token);

            if ($claims !== null) {
                return $claims;
            }
        }

        return null;
    }
}
