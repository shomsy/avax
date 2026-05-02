<?php

declare(strict_types=1);

namespace Avax\Components\Security\System\System\Capabilities\Csrf;

final readonly class CsrfToken
{
    private const string SESSION_KEY = '_token';

    public static function token(): string
    {
        return self::generate();
    }

    public static function generate(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE && PHP_SAPI !== 'cli') {
            session_start();
        }

        if (! isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(length: 32));
        }

        return $_SESSION[self::SESSION_KEY];
    }

    public static function rotate(): string
    {
        $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(length: 32));

        return $_SESSION[self::SESSION_KEY];
    }
}
