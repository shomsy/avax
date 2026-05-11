<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Capabilities\RouteCollection;

use Avax\Components\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;

final class RouteCollection
{
    /** @var list<RouteDefinition> */
    private array $routes = [];

    /** @var array<string, RouteDefinition> */
    private array $namedRoutes = [];

    private ?RouteDefinition $fallback = null;

    public function add(RouteDefinition $route) : void
    {
        $this->routes[] = $route;

        if ($route->name() !== '') {
            $this->namedRoutes[$route->name()] = $route;
        }
    }

    /** @return list<RouteDefinition> */
    public function all() : array
    {
        return $this->routes;
    }

    public function getByName(string $name) : ?RouteDefinition
    {
        return $this->namedRoutes[$name] ?? null;
    }

    /** @return array<string, string> */
    public function getNamedRoutes() : array
    {
        $result = [];
        foreach ($this->namedRoutes as $name => $route) {
            $result[$name] = $route->uri();
        }

        return $result;
    }

    public function setFallback(RouteDefinition $route) : void
    {
        $this->fallback = $route;
    }

    public function getFallback() : ?RouteDefinition
    {
        return $this->fallback;
    }

    public function hasFallback() : bool
    {
        return $this->fallback !== null;
    }

    public function clear() : void
    {
        $this->routes      = [];
        $this->namedRoutes = [];
        $this->fallback    = null;
    }

    /** @param list<RouteDefinition> $routes */
    public function replaceWith(array $routes) : void
    {
        $this->routes      = $routes;
        $this->namedRoutes = [];
        foreach ($routes as $route) {
            if ($route->name() !== '') {
                $this->namedRoutes[$route->name()] = $route;
            }
        }
    }
}
