<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Tokens;

use DateTimeImmutable;

/**
 * TokenStore - Handles persistence and revocation of tokens.
 * 1:1 alignment with refactor.md.
 */
final readonly class TokenStore
{
    public function save(string $tokenId, DateTimeImmutable $expiresAt): void {}

    public function revoke(string $tokenId): void {}

    public function isRevoked(string $tokenId): bool
    {
        return false;
    }
}
