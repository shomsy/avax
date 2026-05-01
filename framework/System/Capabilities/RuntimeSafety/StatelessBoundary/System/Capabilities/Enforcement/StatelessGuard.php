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

final class StatefulDependencyDetector
{
    /** @var array<string, Closure> */
    private array $detectors = [];

    public function register(string $dependency, Closure $detector) : void
    {
        $this->detectors[$dependency] = $detector;
    }

    public function isStateful(string $dependency) : bool
    {
        $detector = $this->detectors[$dependency] ?? null;

        return $detector ? $detector() : false;
    }
}
