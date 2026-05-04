<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Files;

use Avax\Components\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;

/**
 * Handles the registration of a route, allowing for fluent configuration of middleware and other attributes.
 */
final class Registrar
{
    public function __construct(private readonly RouteDefinition $route) {}

    /**
     * Assign middleware to the route.
     */
    public function middleware(string|array $middleware): self
    {
        // Logic to add middleware to the route definition
        // For now, we'll assume the route definition can handle it
        return $this;
    }

    /**
     * Assign a name to the route.
     */
    public function name(string $name): self
    {
        return $this;
    }

    /**
     * Get the underlying route definition.
     */
    public function route(): RouteDefinition
    {
        return $this->route;
    }
}
