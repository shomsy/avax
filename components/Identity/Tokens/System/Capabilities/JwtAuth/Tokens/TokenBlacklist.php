<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Capabilities\JwtAuth\Tokens;

final class TokenBlacklist
{
    /** @var array<string, true> */
    private array $revoked = [];

    public function revoke(string $token) : void
    {
        $this->revoked[$token] = true;
    }

    public function isRevoked(string $token) : bool
    {
        return isset($this->revoked[$token]);
    }

    public function clear() : void
    {
        $this->revoked = [];
    }
}
