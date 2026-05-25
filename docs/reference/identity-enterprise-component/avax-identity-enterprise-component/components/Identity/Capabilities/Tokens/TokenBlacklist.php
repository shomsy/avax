<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Capabilities\Tokens;

use Avax\Components\Identity\Foundation\Values\TokenId;

interface TokenBlacklist
{
    public function contains(TokenId $tokenId): bool;

    public function add(TokenId $tokenId): void;
}
