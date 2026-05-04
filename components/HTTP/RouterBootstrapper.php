<?php

declare(strict_types=1);

namespace Avax\Components\HTTP;

use Avax\Components\HTTP\Dispatcher\System\PublicSurface\ControllerDispatcher;
use Avax\Components\HTTP\Middleware\MiddlewareInterface;
use Avax\Components\HTTP\Response\ResponseFactory;
use Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Files\Registrar;
use Avax\Components\HTTP\Router\System\PublicSurface\RouterInterface;
use Avax\Components\HTTP\Router\System\PublicSurface\RouterRuntimeInterface;
use Avax\Components\HTTP\System\Capabilities\Kernel\AppKernel;
use InvalidArgumentException;
use LogicException;

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
    public array $globalMiddleware
        = [] {
            get {
                return $this->globalMiddleware;
            }
        }

    private readonly RouterInterface $router;

    private readonly ?RouterRuntimeInterface $routerRuntime;

    private array $routeMiddleware
        = [] {
            get {
                return $this->routeMiddleware;
            }
        }

    private array $middlewareGroups = [];

    private array $activeMiddleware = [];

    private array $groupPrefixes = [];

    private array $routes = [];

    /**
     * Create bootstrapper with router and route collection.
     */
    public function __construct(RouterInterface $router, ?RouterRuntimeInterface $routerRuntime = null)
    {
        $this->router = $router;
        $this->routerRuntime = $routerRuntime ?? ($router instanceof RouterRuntimeInterface ? $router : null);
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
     */
    public function route(string $method, string $path, callable|array|string $handler) : self
    {
        $method   = strtoupper(string: $method);
        $fullPath = $this->qualifyPath(path: $path);
        $routeKey = $this->routeKey(method: $method, path: $fullPath);
        $routeRegistrarProxy = $this->registerWithDsl(method: $method, path: $fullPath, handler: $handler);
        $routeMiddleware = array_values(array_merge($this->activeMiddleware, $this->routeMiddleware[$routeKey] ?? []));

        if ($routeMiddleware !== []) {
            $routeRegistrarProxy->middleware(middleware: $routeMiddleware);
        }

        $this->routes[$routeKey] = [
            'method'  => $method,
            'path'    => $fullPath,
            'handler' => $handler,
            'middleware' => $routeMiddleware,
        ];

        return $this;
    }

    private function qualifyPath(string $path) : string
    {
        $prefix = '';

        foreach ($this->groupPrefixes as $groupPrefix) {
            if ($groupPrefix === '') {
                continue;
            }

            $prefix .= '/' . trim(string: (string) $groupPrefix, characters: '/');
        }

        $qualifiedPath = trim(string: $prefix . '/' . ltrim(string: $path, characters: '/'), characters: '/');

        return '/' . $qualifiedPath;
    }

    private function routeKey(string $method, string $path) : string
    {
        return $method . ' ' . $path;
    }

    private function registerWithDsl(string $method, string $path, callable|array|string $handler) : Registrar
    {
        return match ($method) {
            'GET'    => $this->router->get(path: $path, action: $handler),
            'POST'   => $this->router->post(path: $path, action: $handler),
            'PUT'    => $this->router->put(path: $path, action: $handler),
            'PATCH'  => $this->router->patch(path: $path, action: $handler),
            'DELETE' => $this->router->delete(path: $path, action: $handler),
            'OPTIONS' => $this->router->options(path: $path, action: $handler),
            'HEAD'   => $this->router->head(path: $path, action: $handler),
            default => throw new InvalidArgumentException(message: sprintf("Unsupported HTTP method '%s'.", $method)),
        };
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
     * Register PATCH route.
     */
    public function patch(string $path, callable|array|string $handler) : self
    {
        return $this->route(method: 'PATCH', path: $path, handler: $handler);
    }

    /**
     * Register DELETE route.
     */
    public function delete(string $path, callable|array|string $handler) : self
    {
        return $this->route(method: 'DELETE', path: $path, handler: $handler);
    }

    /**
     * Assign middleware to a specific route.
     *
     * @param string                                        $routeKey
     * @param MiddlewareInterface|list<MiddlewareInterface> $middleware
     */
    public function middleware(string $routeKey, MiddlewareInterface|array $middleware) : self
    {
        $middlewareArray = is_array(value: $middleware) ? $middleware : [$middleware];
        $this->routeMiddleware[$routeKey] = array_merge(
            $this->routeMiddleware[$routeKey] ?? [],
            $middlewareArray,
        );

        return $this;
    }

    /**
     * Group routes with common middleware or prefix.
     *
     * @param callable(self): void           $routes
     * @param list<MiddlewareInterface>|null $middleware
     */
    public function group(callable $routes, ?array $middleware = null, string $prefix = '') : self
    {
        $middleware ??= [];
        $previousMiddleware = $this->activeMiddleware;
        $previousPrefixes = $this->groupPrefixes;

        $this->activeMiddleware = array_values(array_merge(
                                                   $this->activeMiddleware,
                                                   $middleware,
                                               ));
        $this->groupPrefixes[]  = $prefix;

        $routes($this);

        $this->activeMiddleware = $previousMiddleware;
        $this->groupPrefixes    = $previousPrefixes;

        return $this;
    }

    /**
     * Define a middleware group for reuse.
     *
     * @param list<MiddlewareInterface> $middleware
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
            throw new InvalidArgumentException(message: sprintf("Middleware group '%s' not defined.", $name));
        }

        $this->activeMiddleware = array_values(array_merge(
                                                   $this->activeMiddleware,
                                                   $this->middlewareGroups[$name],
                                               ));

        return $this;
    }

    /**
     * Set global middleware applied to all routes.
     *
     * @param list<MiddlewareInterface> $middleware
     */
    public function globalMiddleware(array $middleware) : self
    {
        $this->globalMiddleware = $middleware;

        return $this;
    }

    /**
     * Get all registered routes.
     *
     * @return list<array<string, mixed>>
     */
    public function getRoutes() : array
    {
        return array_values(array: $this->routes);
    }

    /**
     * Create a complete HTTP application with routes and middleware.
     */
    public function createApp(
        ResponseFactory $responseFactory,
    ) : AppKernel
    {
        $routerRuntime = $this->bootstrap();

        return new AppKernel(
            routerRuntime   : $routerRuntime,
            responseFactory : $responseFactory,
            globalMiddleware: $this->globalMiddleware,
        );
    }

    /**
     * Bootstrap the router with registered routes.
     */
    public function bootstrap() : RouterRuntimeInterface
    {
        if (! $this->routerRuntime instanceof RouterRuntimeInterface) {
            throw new LogicException(
                message: 'RouterBootstrapper requires a runtime router instance to build an AppKernel.',
            );
        }

        return $this->routerRuntime;
    }
}
