<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Capabilities\Tokens;

use Avax\Components\Identity\Foundation\State\ResettableIdentityState;
use Avax\Components\Identity\Foundation\Values\TokenId;

final class InMemoryTokenBlacklist implements TokenBlacklist, ResettableIdentityState
{
    /** @var array<string, true> */
    private array $revoked = [];

    public function contains(TokenId $tokenId): bool
    {
        return isset($this->revoked[$tokenId->toString()]);
    }

    public function add(TokenId $tokenId): void
    {
        $this->revoked[$tokenId->toString()] = true;
    }

    public function reset(): void
    {
        $this->revoked = [];
    }
}
