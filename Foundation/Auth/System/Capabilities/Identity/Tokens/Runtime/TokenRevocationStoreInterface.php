<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Tokens\Runtime;

use DateTimeImmutable;

/**
 * Tracks revoked access tokens until their expiry.
 */
interface TokenRevocationStoreInterface
{
    public function revoke(string $tokenId, DateTimeImmutable $expiresAt) : void;

    public function isRevoked(string $tokenId, DateTimeImmutable $moment) : bool;
}
