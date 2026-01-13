<?php

declare(strict_types=1);

namespace Avax\HTTP;

use Avax\HTTP\Dispatcher\ControllerDispatcher;
use Avax\HTTP\Middleware\MiddlewareInterface;
use Avax\HTTP\Router\RouteCollection;
use Avax\HTTP\Router\RouterInterface;
use Avax\HTTP\Router\Routing\RouteCollection;
use Avax\HTTP\Router\Routing\RouteDefinition;
use InvalidArgumentException;

/**
 * Router Bootstrapper - Route Registration and Middleware Configuration
 *
 * Provides a clean API for setting up routes and their associated middleware
 * in a structured, configuration-driven way.
 *
 * Features:
 * - Route registration with fluent API
 * - Middleware assignment per route or route groups
 * - Route grouping and nesting
 * - Middleware priority management
 */
final class RouterBootstrapper
{
    private RouterInterface $router;

    private RouteCollection $routeCollection;

    private array $globalMiddleware = [];

    private array $routeMiddleware = [];

    private array $middlewareGroups = [];

    /**
     * Create bootstrapper with router and route collection.
     */
    public function __construct(RouterInterface $router, RouteCollection $routeCollection)
    {
        $this->router          = $router;
        $this->routeCollection = $routeCollection;
    }

    /**
     * Register multiple routes for different HTTP methods on the same path.
     */
    public function match(array $methods, string $path, callable|array|string $handler) : self
    {
        foreach ($methods as $method) {
            $this->route(method: $method, path: $path, handler: $handler);
        }

        return $this;
    }

    /**
     * Register a route with optional middleware.
     *
     * @throws \Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException
     */
    public function route(string $method, string $path, callable|array|string $handler) : self
    {
        $route = new RouteDefinition(method: $method, path: $path, action: $handler);

        // Apply route-specific middleware if configured
        if (isset($this->routeMiddleware[$method . ' ' . $path])) {
            $route = $route->withMiddleware($this->routeMiddleware[$method . ' ' . $path]);
        }

        $this->routeCollection->addRoute($route);

        return $this;
    }

    /**
     * Register GET route.
     */
    public function get(string $path, callable|array|string $handler) : self
    {
        return $this->route(method: 'GET', path: $path, handler: $handler);
    }

    /**
     * Register POST route.
     */
    public function post(string $path, callable|array|string $handler) : self
    {
        return $this->route(method: 'POST', path: $path, handler: $handler);
    }

    /**
     * Register PUT route.
     */
    public function put(string $path, callable|array|string $handler) : self
    {
        return $this->route(method: 'PUT', path: $path, handler: $handler);
    }

    /**
     * Register DELETE route.
     */
    public function delete(string $path, callable|array|string $handler) : self
    {
        return $this->route(method: 'DELETE', path: $path, handler: $handler);
    }

    /**
     * Register PATCH route.
     */
    public function patch(string $path, callable|array|string $handler) : self
    {
        return $this->route(method: 'PATCH', path: $path, handler: $handler);
    }

    /**
     * Group routes with common middleware or prefix.
     */
    public function group(callable $routes, array|null $middleware = null, string $prefix = '') : self
    {
        $middleware         ??= [];
        $previousMiddleware = $this->routeMiddleware;

        // Add group middleware to current middleware stack
        foreach ($middleware as $mw) {
            $this->routeMiddleware[] = $mw;
        }

        // Execute routes in group
        $routes($this);

        // Restore previous middleware stack
        $this->routeMiddleware = $previousMiddleware;

        return $this;
    }

    /**
     * Define a middleware group for reuse.
     */
    public function middlewareGroup(string $name, array $middleware) : self
    {
        $this->middlewareGroups[$name] = $middleware;

        return $this;
    }

    /**
     * Apply middleware group to current routes.
     */
    public function useGroup(string $name) : self
    {
        if (! isset($this->middlewareGroups[$name])) {
            throw new InvalidArgumentException(message: "Middleware group '{$name}' not defined.");
        }

        $this->routeMiddleware = array_merge($this->routeMiddleware, $this->middlewareGroups[$name]);

        return $this;
    }

    /**
     * Assign middleware to a specific route.
     */
    public function middleware(string $routeKey, MiddlewareInterface|array $middleware) : self
    {
        $middlewareArray                  = is_array($middleware) ? $middleware : [$middleware];
        $this->routeMiddleware[$routeKey] = array_merge(
            $this->routeMiddleware[$routeKey] ?? [],
            $middlewareArray
        );

        return $this;
    }

    /**
     * Set global middleware applied to all routes.
     */
    public function globalMiddleware(array $middleware) : self
    {
        $this->globalMiddleware = $middleware;

        return $this;
    }

    /**
     * Get all registered routes.
     */
    public function getRoutes() : array
    {
        return $this->routeCollection->getRoutes();
    }

    /**
     * Get global middleware.
     */
    public function getGlobalMiddleware() : array
    {
        return $this->globalMiddleware;
    }

    /**
     * Get route-specific middleware.
     */
    public function getRouteMiddleware() : array
    {
        return $this->routeMiddleware;
    }

    /**
     * Create a complete HTTP application with routes and middleware.
     */
    public function createApp(
        ControllerDispatcher $dispatcher,
        ResponseFactory      $responseFactory
    ) : AppKernel
    {
        $router = $this->bootstrap();

        return new AppKernel(
            router          : $router,
            dispatcher      : $dispatcher,
            responseFactory : $responseFactory,
            globalMiddleware: $this->globalMiddleware
        );
    }

    /**
     * Bootstrap the router with registered routes.
     */
    public function bootstrap() : RouterInterface
    {
        // In a full implementation, this would configure the router
        // with the route collection and middleware
        return $this->router;
    }
}
