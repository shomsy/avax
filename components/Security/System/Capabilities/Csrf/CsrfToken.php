<?php

declare(strict_types=1);

namespace Avax\Components\Security\System\Capabilities\Csrf;

final readonly class CsrfToken
{
    private static string $sessionKey = '_token';

    public static function token() : string
    {
        return self::generate();
    }

    public static function generate() : string
    {
        if (! isset($_SESSION[self::$sessionKey])) {
            $_SESSION[self::$sessionKey] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::$sessionKey];
    }
}

final readonly class CsrfVerifier
{
    public static function verify(string $token, string|null $sessionToken = null) : bool
    {
        return hash_equals(
            $sessionToken ?? $_SESSION['_token'] ?? '',
            $token,
        );
    }
}