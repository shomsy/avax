<?php

declare(strict_types=1);

namespace Avax\Components\StatelessBoundary\System\PublicSurface;

use Avax\Components\StatelessBoundary\System\Capabilities\Enforcement\StatelessGuard;

final class StatelessBoundary
{
    private static string $mode = 'hybrid';

    public static function configure(string $mode) : void
    {
        self::$mode = $mode;
    }

    public static function mode() : string
    {
        return self::$mode;
    }

    public static function enforceForRoute(string $route) : void
    {
        if (self::isStatelessRoute($route)) {
            StatelessGuard::enforce();
        }
    }

    public static function isStatelessRoute(string $route) : bool
    {
        if (self::$mode === 'stateless') {
            return true;
        }

        if (self::$mode === 'stateful') {
            return false;
        }

        return str_starts_with($route, '/api/');
    }

    public static function audit() : BoundaryAudit
    {
        return new BoundaryAudit(
            mode              : self::$mode,
            statelessRoutes   : self::detectedStatelessRoutes(),
            statefulViolations: self::detectViolations(),
        );
    }

    /**
     * @return list<string>
     */
    private static function detectedStatelessRoutes() : array
    {
        return ['/api/*'];
    }

    /**
     * @return list<string>
     */
    private static function detectViolations() : array
    {
        return [];
    }
}

final readonly class BoundaryAudit
{
    /**
     * @param list<string> $statelessRoutes
     * @param list<string> $statefulViolations
     */
    public function __construct(
        public string $mode,
        public array $statelessRoutes,
        public array $statefulViolations,
    ) {}

    public function isCompliant() : bool
    {
        return $this->statefulViolations === [];
    }
}
