<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Blacklist;

use Avax\Components\Identity\Auth\System\Foundation\Ids\TokenId;

/**
 * TokenBlacklist — contract for token revocation checks during verification.
 *
 * Adapted from the enterprise reference package.
 * Simpler than TokenRevocationStoreInterface: only checks presence, not expiry.
 * Used by the VerifyAccessToken flow for runtime token verification.
 */
interface TokenBlacklist
{
    public function contains(TokenId $tokenId): bool;

    public function add(TokenId $tokenId): void;
}
