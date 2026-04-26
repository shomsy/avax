<?php

declare(strict_types=1);

namespace Avax\HTTP;

use Avax\HTTP\Dispatcher\ControllerDispatcher;
use Avax\HTTP\Middleware\MiddlewareInterface;
use Avax\HTTP\Response\ResponseFactory;
use Avax\HTTP\Router\RouterInterface;
use Avax\HTTP\Router\RouterRuntimeInterface;
use Avax\HTTP\Router\System\Flows\RegisterRoutes\Files\RouteRegistrarProxy;
use InvalidArgumentException;
use LogicException;
use ReflectionException;

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
    private RouterInterface $router;
    private RouterRuntimeInterface|null $runtimeRouter;
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
    public function __construct(RouterInterface $router, RouterRuntimeInterface|null $runtimeRouter = null)
    {
        $this->router        = $router;
        $this->runtimeRouter = $runtimeRouter ?? ($router instanceof RouterRuntimeInterface ? $router : null);
    }

    /**
     * Register multiple routes for different HTTP methods on the same path.
     *
     * @param array                 $methods
     * @param string                $path
     * @param callable|array|string $handler
     *
     * @return RouterBootstrapper
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
     */
    public function route(string $method, string $path, callable|array|string $handler) : self
    {
        $method          = strtoupper(string: $method);
        $fullPath        = $this->qualifyPath(path: $path);
        $routeKey        = $this->routeKey(method: $method, path: $fullPath);
        $proxy           = $this->registerWithDsl(method: $method, path: $fullPath, handler: $handler);
        $routeMiddleware = array_values(array_merge(
                                            $this->activeMiddleware,
                                            $this->routeMiddleware[$routeKey] ?? []
                                        ));

        if ($routeMiddleware !== []) {
            $proxy->middleware(middleware: $routeMiddleware);
        }

        $this->routes[$routeKey] = [
            'method'     => $method,
            'path'       => $fullPath,
            'handler'    => $handler,
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

            $prefix .= '/' . trim(string: $groupPrefix, characters: '/');
        }

        $qualifiedPath = trim(string: $prefix . '/' . ltrim(string: $path, characters: '/'), characters: '/');

        return '/' . $qualifiedPath;
    }

    private function routeKey(string $method, string $path) : string
    {
        return $method . ' ' . $path;
    }

    private function registerWithDsl(string $method, string $path, callable|array|string $handler) : RouteRegistrarProxy
    {
        return match ($method) {
            'GET'     => $this->router->get(path: $path, action: $handler),
            'POST'    => $this->router->post(path: $path, action: $handler),
            'PUT'     => $this->router->put(path: $path, action: $handler),
            'PATCH'   => $this->router->patch(path: $path, action: $handler),
            'DELETE'  => $this->router->delete(path: $path, action: $handler),
            'OPTIONS' => $this->router->options(path: $path, action: $handler),
            'HEAD'    => $this->router->head(path: $path, action: $handler),
            default   => throw new InvalidArgumentException(message: "Unsupported HTTP method '{$method}'."),
        };
    }

    /**
     * Register GET route.
     *
     * @param string                $path
     * @param callable|array|string $handler
     *
     * @return RouterBootstrapper
     */
    public function get(string $path, callable|array|string $handler) : self
    {
        return $this->route(method: 'GET', path: $path, handler: $handler);
    }

    /**
     * Register POST route.
     *
     * @param string                $path
     * @param callable|array|string $handler
     *
     * @return RouterBootstrapper
     */
    public function post(string $path, callable|array|string $handler) : self
    {
        return $this->route(method: 'POST', path: $path, handler: $handler);
    }

    /**
     * Register PUT route.
     *
     * @param string                $path
     * @param callable|array|string $handler
     *
     * @return RouterBootstrapper
     */
    public function put(string $path, callable|array|string $handler) : self
    {
        return $this->route(method: 'PUT', path: $path, handler: $handler);
    }

    /**
     * Register PATCH route.
     *
     * @param string                $path
     * @param callable|array|string $handler
     *
     * @return RouterBootstrapper
     */
    public function patch(string $path, callable|array|string $handler) : self
    {
        return $this->route(method: 'PATCH', path: $path, handler: $handler);
    }

    /**
     * Register DELETE route.
     *
     * @param string                $path
     * @param callable|array|string $handler
     *
     * @return RouterBootstrapper
     */
    public function delete(string $path, callable|array|string $handler) : self
    {
        return $this->route(method: 'DELETE', path: $path, handler: $handler);
    }

    /**
     * Assign middleware to a specific route.
     */
    public function middleware(string $routeKey, MiddlewareInterface|array $middleware) : self
    {
        $middlewareArray                  = is_array(value: $middleware) ? $middleware : [$middleware];
        $this->routeMiddleware[$routeKey] = array_merge(
            $this->routeMiddleware[$routeKey] ?? [],
            $middlewareArray
        );

        return $this;
    }

    /**
     * Group routes with common middleware or prefix.
     */
    public function group(callable $routes, array|null $middleware = null, string $prefix = '') : self
    {
        $middleware         ??= [];
        $previousMiddleware = $this->activeMiddleware;
        $previousPrefixes   = $this->groupPrefixes;

        $this->activeMiddleware = array_values(array_merge(
                                                   $this->activeMiddleware,
                                                   $middleware
                                               ));
        $this->groupPrefixes[]  = $prefix;

        $routes($this);

        $this->activeMiddleware = $previousMiddleware;
        $this->groupPrefixes    = $previousPrefixes;

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

        $this->activeMiddleware = array_values(array_merge(
                                                   $this->activeMiddleware,
                                                   $this->middlewareGroups[$name]
                                               ));

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
        return array_values(array: $this->routes);
    }

    /**
     * Create a complete HTTP application with routes and middleware.
     *
     * @throws ReflectionException
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
    public function bootstrap() : RouterRuntimeInterface
    {
        if ($this->runtimeRouter === null) {
            throw new LogicException(
                message: 'RouterBootstrapper requires a runtime router instance to build an AppKernel.'
            );
        }

        return $this->runtimeRouter;
    }
}
