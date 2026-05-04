<?php

declare(strict_types=1);

namespace Avax\Components\StatelessBoundary\System\Capabilities\Enforcement;

use RuntimeException;

final readonly class StatelessGuard
{
    public static function enforce() : void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            throw new RuntimeException(
                'Session access violated in stateless route: session_start() called',
            );
        }
    }

    public static function detectSessionRead() : bool
    {
        return isset($_SESSION);
    }

    public static function detectCookieWrite() : bool
    {
        return ! headers_sent();
    }
}
