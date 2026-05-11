<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Files;

use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteCollection;
use Avax\Components\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;

/**
 * Handles the registration of a route, allowing for fluent configuration of middleware and other attributes.
 */
final class Registrar
{
    private RouteCollection $collection;
    private RouteDefinition $originalRoute;

    /** @var list<string|callable> */
    private array $middleware = [];

    private string $name = '';

    public function __construct(RouteCollection $collection, RouteDefinition $route)
    {
        $this->collection    = $collection;
        $this->originalRoute = $route;
    }

    /**
     * Assign middleware to the route.
     *
     * @param string|callable|list<string|callable> $middleware
     */
    public function middleware(string|array|callable $middleware) : self
    {
        $items            = is_array($middleware) ? array_values($middleware) : [$middleware];
        $this->middleware = [...$this->middleware, ...$items];

        $this->updateRouteInCollection();

        return $this;
    }

    /**
     * Assign a name to the route.
     */
    public function name(string $name) : self
    {
        $this->name = $name;

        $this->updateRouteInCollection();

        return $this;
    }

    /**
     * Get the underlying route definition with applied name and middleware.
     */
    public function route() : RouteDefinition
    {
        $resolved = $this->originalRoute;

        if ($this->name !== '') {
            $resolved = $resolved->withName($this->name);
        }

        if ($this->middleware !== []) {
            $resolved = $resolved->withMiddleware($this->middleware);
        }

        return $resolved;
    }

    private function updateRouteInCollection() : void
    {
        $resolved = $this->originalRoute;

        if ($this->name !== '') {
            $resolved = $resolved->withName($this->name);
        }

        if ($this->middleware !== []) {
            $resolved = $resolved->withMiddleware($this->middleware);
        }

        // Replace the route in the collection by finding it by object identity.
        // Since RouteDefinition is readonly, we replace it by rebuilding the collection entry.
        $allRoutes = $this->collection->all();
        foreach ($allRoutes as $index => $existing) {
            if ($existing === $this->originalRoute) {
                $allRoutes[$index] = $resolved;

                $this->collection->replaceWith($allRoutes);

                break;
            }
        }
    }
}
