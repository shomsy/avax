<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Capabilities\JwtAuth;

final class TokenBlacklist
{
    /**
     * @var array<string, true>
     */
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

    /**
     * Reset static state for long-lived worker safety.
     */
    public static function reset(): void
    {
        self::$revoked = [];
    }
}
