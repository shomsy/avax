<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Store;

use DateTimeImmutable;
use SensitiveParameter;

/**
 * Persisted denylist for revoked access tokens.
 */
interface TokenRevocationStoreInterface
{
    public function revoke(#[SensitiveParameter] string $tokenId, DateTimeImmutable $expiresAt): void;

    public function isRevoked(#[SensitiveParameter] string $tokenId, DateTimeImmutable $moment): bool;
}
