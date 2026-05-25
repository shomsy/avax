<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Blacklist;

use Avax\Components\Identity\Auth\System\Foundation\Ids\TokenId;
use Avax\Components\Identity\Auth\System\Foundation\State\ResettableIdentityState;

/**
 * InMemoryTokenBlacklist — in-memory token denylist for runtime verification.
 *
 * Adapted from the enterprise reference package.
 * Implements ResettableIdentityState for long-lived worker safety.
 * Does NOT perform TTL-based eviction — tokens stay revoked until reset.
 * For production with expiry-based eviction, use a Redis or database-backed implementation.
 */
final class InMemoryTokenBlacklist implements TokenBlacklist, ResettableIdentityState
{
    /** @var array<string, true> */
    private array $revoked = [];

    public function contains(TokenId $tokenId): bool
    {
        return isset($this->revoked[$tokenId->value]);
    }

    public function add(TokenId $tokenId): void
    {
        $this->revoked[$tokenId->value] = true;
    }

    public function reset(): void
    {
        $this->revoked = [];
    }
}
