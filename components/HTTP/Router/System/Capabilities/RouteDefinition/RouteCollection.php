<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\System\Capabilities\RouteDefinition;

use Avax\HTTP\Router\System\Foundation\Exceptions\DuplicateRouteException;

/**
 * Canonical route collection implementing single source-of-truth for routing.
 *
 * Provides unified storage for both exact path matches and pattern-based routes,
 * ensuring deterministic behavior across different loading strategies.
 */
final class RouteCollection
{
    /**
     * Routes indexed by method and path for fast exact lookups.
     * Structure: [$method][$path] = RouteDefinition (for exact matches)
     *
     * @var array<string, array<string, RouteDefinition>>
     */
    private array $exactRoutes = [];

    /**
     * Pattern-based routes indexed by method for sequential matching.
     * Structure: [$method][] = RouteDefinition (for pattern routes)
     *
     * @var array<string, RouteDefinition[]>
     */
    private array $patternRoutes = [];

    /**
     * Route keys for deduplication tracking.
     * Format: "METHOD|DOMAIN|PATH"
     *
     * @var array<string, true>
     */
    private array $routeKeys = [];

    /**
     * Add a route to the collection with deduplication checking.
     *
     * @throws DuplicateRouteException
     */
    public function addRoute(RouteDefinition $route) : void
    {
        $key = $this->generateRouteKey(route: $route);

        if (isset($this->routeKeys[$key])) {
            throw new DuplicateRouteException(
                method: $route->method,
                path  : $route->path,
                domain: $route->domain,
                name  : $route->name ?: null
            );
        }

        $this->routeKeys[$key] = true;

        $method = strtoupper(string: $route->method);

        // Check if path contains parameters (indicating pattern route)
        if ($this->isPatternRoute(path: $route->path)) {
            $this->patternRoutes[$method][] = $route;
        } else {
            // Exact path match
            $this->exactRoutes[$method][$route->path] = $route;
        }
    }

    /**
     * Generate unique key for route deduplication.
     */
    private function generateRouteKey(RouteDefinition $route) : string
    {
        return sprintf(
            '%s|%s|%s',
            strtoupper(string: $route->method),
            $route->domain ?? '',
            $route->path
        );
    }

    /**
     * Check if a path contains route parameters (indicating pattern route).
     */
    private function isPatternRoute(string $path) : bool
    {
        return str_contains(haystack: $path, needle: '{') && str_contains(haystack: $path, needle: '}');
    }

    /**
     * Find exact route match for method and path.
     */
    public function findExactRoute(string $method, string $path) : RouteDefinition|null
    {
        return $this->exactRoutes[strtoupper(string: $method)][$path] ?? null;
    }

    /**
     * Get all pattern routes for a method.
     *
     * @return RouteDefinition[]
     */
    public function getPatternRoutes(string $method) : array
    {
        return $this->patternRoutes[strtoupper(string: $method)] ?? [];
    }

    /**
     * Get all routes grouped by method (legacy format for backward compatibility).
     *
     * @return array<string, RouteDefinition[]>
     */
    public function getAllRoutes() : array
    {
        $allRoutes = [];

        foreach (array_keys(array: $this->exactRoutes + $this->patternRoutes) as $method) {
            $allRoutes[$method] = $this->getAllRoutesForMethod(method: $method);
        }

        return $allRoutes;
    }

    /**
     * Get all routes for a method (exact + patterns).
     *
     * @return RouteDefinition[]
     */
    public function getAllRoutesForMethod(string $method) : array
    {
        $method = strtoupper(string: $method);
        $routes = [];

        // Add exact routes
        foreach ($this->exactRoutes[$method] ?? [] as $route) {
            $routes[] = $route;
        }

        // Add pattern routes
        foreach ($this->patternRoutes[$method] ?? [] as $route) {
            $routes[] = $route;
        }

        return $routes;
    }

    /**
     * Get statistics about the route collection.
     *
     * @return array{exact: int, patterns: int, total: int}
     */
    public function getStatistics() : array
    {
        $exactCount = 0;
        foreach ($this->exactRoutes as $routes) {
            $exactCount += count(value: $routes);
        }

        $patternCount = 0;
        foreach ($this->patternRoutes as $routes) {
            $patternCount += count(value: $routes);
        }

        return [
            'exact'    => $exactCount,
            'patterns' => $patternCount,
            'total'    => $exactCount + $patternCount,
        ];
    }

    /**
     * Clear all routes (for testing/reinitialization).
     */
    public function clear() : void
    {
        $this->exactRoutes   = [];
        $this->patternRoutes = [];
        $this->routeKeys     = [];
    }
}