<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Capabilities\RouteCollection;

final class RouteCollection
{
    /** @var list<RouteDefinition> */
    private array $routes = [];

    public function add(RouteDefinition $route): void
    {
        $this->routes[] = $route;
    }

    /** @return list<RouteDefinition> */
    public function all(): array
    {
        return $this->routes;
    }
}
