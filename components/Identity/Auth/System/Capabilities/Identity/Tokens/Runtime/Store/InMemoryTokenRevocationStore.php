<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\Tokens\Runtime\Store;

use DateTimeImmutable;
use SensitiveParameter;

final class InMemoryTokenRevocationStore implements TokenRevocationStoreInterface
{
    /** @var array<string, DateTimeImmutable> */
    private array $revoked = [];

    public function revoke(#[SensitiveParameter] string $tokenId, DateTimeImmutable $expiresAt) : void
    {
        $this->revoked[$tokenId] = $expiresAt;
    }

    public function isRevoked(#[SensitiveParameter] string $tokenId, DateTimeImmutable $moment) : bool
    {
        if (! isset($this->revoked[$tokenId])) {
            return false;
        }

        if ($this->revoked[$tokenId] < $moment) {
            unset($this->revoked[$tokenId]);

            return false;
        }

        return true;
    }
}
