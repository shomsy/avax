<?php

declare(strict_types=1);

namespace Avax\Components\StatelessBoundary\System\Capabilities\Enforcement;

use Closure;
use RuntimeException;

final readonly class StatelessGuard
{
    public static function enforce() : void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            throw new RuntimeException(
                'Session access violated in stateless route: session_start() called'
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

final readonly class StatefulServiceDetector
{
    private static array $detectors = [];

    public static function register(string $service, Closure $detector) : void
    {
        self::$detectors[$service] = $detector;
    }

    public static function isStateful(string $service) : bool
    {
        $detector = self::$detectors[$service] ?? null;

        return $detector ? $detector() : false;
    }
}