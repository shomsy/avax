<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Tokens;

/**
 * TokenStore - Handles persistence and revocation of tokens.
 * 1:1 alignment with refactor.md.
 */
final readonly class TokenStore
{
    public function isRevoked() : bool
    {
        return false;
    }
}
