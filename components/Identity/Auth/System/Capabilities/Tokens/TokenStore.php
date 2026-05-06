<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Tokens;

use DateTimeImmutable;
use SensitiveParameter;

/**
 * TokenStore - Handles persistence and revocation of tokens.
 * 1:1 alignment with refactor.md.
 */
final class TokenStore
{
    /** @var array<string, DateTimeImmutable> */
    private array $revokedTokens = [];

    public function revoke(#[SensitiveParameter] string $tokenId, DateTimeImmutable $expiresAt): void
    {
        $this->revokedTokens[$tokenId] = $expiresAt;
    }

    public function isRevoked(#[SensitiveParameter] string $tokenId = '', ?DateTimeImmutable $moment = null): bool
    {
        if ($tokenId === '' || ! isset($this->revokedTokens[$tokenId])) {
            return false;
        }

        $moment ??= new DateTimeImmutable();

        if ($this->revokedTokens[$tokenId] <= $moment) {
            unset($this->revokedTokens[$tokenId]);

            return false;
        }

        return true;
    }
}
