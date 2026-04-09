<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Token;

use DateTimeImmutable;

/**
 * In-memory access token revocation store.
 */
final class InMemoryTokenRevocationStore implements TokenRevocationStoreInterface
{
    /** @var array<string, int> */
    private array $revokedUntil = [];

    public function revoke(string $tokenId, DateTimeImmutable $expiresAt) : void
    {
        $this->revokedUntil[$tokenId] = $expiresAt->getTimestamp();
    }

    public function isRevoked(string $tokenId, DateTimeImmutable $moment) : bool
    {
        $expiresAt = $this->revokedUntil[$tokenId] ?? null;

        return $expiresAt !== null && $expiresAt >= $moment->getTimestamp();
    }
}
