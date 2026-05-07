<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Capabilities\JwtAuth\Tokens;

final class TokenBlacklist
{
    /** @var array<string, true> */
    private static array $revoked = [];

    public function revoke(string $token) : void
    {
        self::$revoked[$token] = true;
    }

    public function isRevoked(string $token) : bool
    {
        return isset(self::$revoked[$token]);
    }

    public function clear() : void
    {
        self::$revoked = [];
    }
}
