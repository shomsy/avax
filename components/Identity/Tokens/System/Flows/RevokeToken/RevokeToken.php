<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Flows\RevokeToken;

use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Codec\TokenCodecInterface;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Store\TokenRevocationStoreInterface;
use DateTimeImmutable;
use SensitiveParameter;

final readonly class RevokeToken
{
    public function __construct(
        private TokenCodecInterface           $tokenCodec,
        private TokenRevocationStoreInterface $tokenRevocationStore,
    ) {}

    public function execute(#[SensitiveParameter] string $token) : void
    {
        $claims = $this->tokenCodec->decode(token: $token);

        if ($claims === null || ! is_string(value: $claims['jti'] ?? null) || ! is_int(value: $claims['exp'] ?? null)) {
            return;
        }

        $this->tokenRevocationStore->revoke(
            tokenId  : $claims['jti'],
            expiresAt: new DateTimeImmutable(datetime: '@' . $claims['exp']),
        );
    }
}
