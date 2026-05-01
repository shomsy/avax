<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\RouteIntelligence;

/**
 * Analyzes routes for conflicts, shadows, and explains matching.
 */
final class RouteAnalyzer
{
    /**
     * @var list<RouteInfo>
     */
    private array $routes = [];

    /**
     * @param list<array{method: string, path: string, handler: mixed, middleware?: list<string>}> $routes
     */
    public function setRoutes(array $routes) : self
    {
        $this->routes = array_map(
            fn (array $route) : RouteInfo => new RouteInfo(
                method    : strtoupper($route['method']),
                pattern   : $route['path'],
                handler   : $this->describeHandler($route['handler']),
                middleware: $route['middleware'] ?? [],
            ),
            $routes,
        );

        return $this;
    }

    private function describeHandler(mixed $handler) : string
    {
        if (is_string($handler)) {
            return $handler;
        }

        if (is_array($handler) && count($handler) === 2) {
            $class = is_object($handler[0]) ? get_class($handler[0]) : $handler[0];

            return "{$class}::{$handler[1]}";
        }

        if (is_callable($handler)) {
            return 'callable';
        }

        return get_debug_type($handler);
    }

    /**
     * @return list<RouteInfo>
     */
    public function routes() : array
    {
        return $this->routes;
    }

    /**
     * Detect route conflicts.
     *
     * @return list<RouteConflict>
     */
    public function detectConflicts() : array
    {
        $conflicts = [];
        $count     = count($this->routes);

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $routeA = $this->routes[$i];
                $routeB = $this->routes[$j];

                if ($routeA->method !== $routeB->method) {
                    continue;
                }

                $conflict = $this->checkConflict($routeA, $routeB);

                if ($conflict !== null) {
                    $conflicts[] = $conflict;
                }
            }
        }

        return $conflicts;
    }

    private function checkConflict(RouteInfo $a, RouteInfo $b) : RouteConflict|null
    {
        if ($a->pattern === $b->pattern) {
            return new RouteConflict(
                type  : RouteConflict::CONFLICT_EXACT,
                routeA: $a,
                routeB: $b,
                reason: sprintf(
                            'Exact duplicate: %s %s',
                            $a->method,
                            $a->pattern,
                        ),
            );
        }

        if ($this->patternsMayConflict($a->pattern, $b->pattern)) {
            return new RouteConflict(
                type  : RouteConflict::CONFLICT_AMBIGUOUS,
                routeA: $a,
                routeB: $b,
                reason: sprintf(
                            "Ambiguous: '%s' and '%s' may match same URLs",
                            $a->pattern,
                            $b->pattern,
                        ),
            );
        }

        return null;
    }

    private function patternsMayConflict(string $pattern1, string $pattern2) : bool
    {
        $parts1 = explode('/', $pattern1);
        $parts2 = explode('/', $pattern2);

        if (count($parts1) !== count($parts2)) {
            return false;
        }

        foreach ($parts1 as $i => $part1) {
            $part2 = $parts2[$i];

            $isParam1 = str_starts_with($part1, '{');
            $isParam2 = str_starts_with($part2, '{');

            if (! $isParam1 && ! $isParam2 && $part1 !== $part2) {
                return false;
            }
        }

        return true;
    }

    /**
     * Explain how a request would be matched.
     */
    public function explainMatch(string $method, string $path) : RouteInfo|null
    {
        $method = strtoupper($method);

        foreach ($this->routes as $route) {
            if ($route->method !== $method) {
                continue;
            }

            if ($this->matchesPattern($route->pattern, $path)) {
                return $route;
            }
        }

        return null;
    }

    private function matchesPattern(string $pattern, string $path) : bool
    {
        $patternParts = explode('/', trim($pattern, '/'));
        $pathParts    = explode('/', trim($path, '/'));

        if (count($patternParts) !== count($pathParts)) {
            return false;
        }

        foreach ($patternParts as $i => $part) {
            if (str_starts_with($part, '{')) {
                $isOptional = str_ends_with($part, '?}');
                $pathPart   = $pathParts[$i] ?? '';

                if ($isOptional && $pathPart === '') {
                    continue;
                }

                if ($pathPart === '') {
                    return false;
                }

                continue;
            }

            if ($part !== ($pathParts[$i] ?? '')) {
                return false;
            }
        }

        return true;
    }

    /**
     * Find unreachable routes (shadowed by more specific routes).
     *
     * @return list<RouteConflict>
     */
    public function findUnreachableRoutes() : array
    {
        $unreachable = [];

        foreach ($this->routes as $early) {
            foreach ($this->routes as $late) {
                if ($early->fingerprint() === $late->fingerprint()) {
                    continue;
                }

                if ($early->method !== $late->method) {
                    continue;
                }

                if ($this->isShadowed($early, $late)) {
                    $unreachable[] = new RouteConflict(
                        type  : RouteConflict::CONFLICT_SHADOW,
                        routeA: $early,
                        routeB: $late,
                        reason: sprintf(
                                    "Route '%s %s' shadows '%s %s'",
                                    $early->method,
                                    $early->pattern,
                                    $late->method,
                                    $late->pattern,
                                ),
                    );
                }
            }
        }

        return $unreachable;
    }

    private function isShadowed(RouteInfo $specific, RouteInfo $general) : bool
    {
        $specificParams = $specific->parameters();
        $generalParams  = $general->parameters();

        if (count($specificParams) === 0 && count($generalParams) > 0) {
            $patternParts1 = explode('/', $specific->pattern);
            $patternParts2 = explode('/', $general->pattern);

            if (count($patternParts1) !== count($patternParts2)) {
                return false;
            }

            foreach ($patternParts2 as $i => $part) {
                if (str_starts_with($part, '{')) {
                    continue;
                }

                if (($patternParts1[$i] ?? '') !== $part) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }
}
