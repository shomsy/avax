<?php

declare(strict_types=1);

namespace Avax\Components\Router\System\Capabilities\RouteDefinition;

use Avax\Components\Router\System\Foundation\Exceptions\DuplicateRouteException;

/**
 * Canonical route collection implementing single source-of-truth for routing.
 */
final class RouteCollection
{
    private array $exactRoutes   = [];
    private array $patternRoutes = [];
    private array $routeKeys     = [];

    public function addRoute(RouteDefinition $route) : void
    {
        $key = $this->generateRouteKey($route);

        if (isset($this->routeKeys[$key])) {
            throw new DuplicateRouteException(
                method: $route->method,
                path  : $route->path,
                domain: $route->domain,
                name  : $route->name ?: null
            );
        }

        $this->routeKeys[$key] = true;
        $method                = strtoupper($route->method);

        if ($this->isPatternRoute($route->path)) {
            $this->patternRoutes[$method][] = $route;
        } else {
            $this->exactRoutes[$method][$route->path] = $route;
        }
    }

    private function generateRouteKey(RouteDefinition $route) : string
    {
        return sprintf('%s|%s|%s', strtoupper($route->method), $route->domain ?? '', $route->path);
    }

    private function isPatternRoute(string $path) : bool
    {
        return str_contains($path, '{') && str_contains($path, '}');
    }

    public function findExactRoute(string $method, string $path) : RouteDefinition|null
    {
        return $this->exactRoutes[strtoupper($method)][$path] ?? null;
    }

    public function getPatternRoutes(string $method) : array
    {
        return $this->patternRoutes[strtoupper($method)] ?? [];
    }

    public function getAllRoutes() : array
    {
        $allRoutes = [];
        foreach (array_keys($this->exactRoutes + $this->patternRoutes) as $method) {
            $allRoutes[$method] = $this->getAllRoutesForMethod($method);
        }

        return $allRoutes;
    }

    public function getAllRoutesForMethod(string $method) : array
    {
        $method = strtoupper($method);

        return array_merge(
            array_values($this->exactRoutes[$method] ?? []),
            $this->patternRoutes[$method] ?? []
        );
    }

    public function getStatistics() : array
    {
        $exactCount   = array_sum(array_map('count', $this->exactRoutes));
        $patternCount = array_sum(array_map('count', $this->patternRoutes));

        return [
            'exact'    => $exactCount,
            'patterns' => $patternCount,
            'total'    => $exactCount + $patternCount,
        ];
    }

    public function clear() : void
    {
        $this->exactRoutes   = [];
        $this->patternRoutes = [];
        $this->routeKeys     = [];
    }
}
