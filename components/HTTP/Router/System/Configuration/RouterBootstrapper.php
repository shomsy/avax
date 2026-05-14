<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Configuration;

use Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Files\Registrar;
use Avax\Components\HTTP\Router\System\PublicSurface\RouterInterface;
use Avax\Components\HTTP\Router\System\PublicSurface\RouterRuntimeInterface;
use Avax\Components\HTTP\System\Capabilities\MiddlewarePipeline\MiddlewareInterface;
use InvalidArgumentException;
use LogicException;

/**
 * Router Bootstrapper - Route Registration and Middleware Configuration
 */
final class RouterBootstrapper
{
    public array $globalMiddleware = [];

    private readonly RouterInterface $router;

    private readonly ?RouterRuntimeInterface $routerRuntime;

    private array $routeMiddleware = [];

    private array $middlewareGroups = [];

    private array $activeMiddleware = [];

    private array $groupPrefixes = [];

    private array $routes = [];

    /**
     * Create bootstrapper with router and optional runtime.
     */
    public function __construct(RouterInterface $router, RouterRuntimeInterface|null $routerRuntime = null)
    {
        $this->router        = $router;
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
        $method          = strtoupper(string: $method);
        $fullPath        = $this->qualifyPath(path: $path);
        $routeKey        = $this->routeKey(method: $method, path: $fullPath);
        $registrar       = $this->registerWithDsl(method: $method, path: $fullPath, handler: $handler);
        $routeMiddleware = array_values(array_merge($this->activeMiddleware, $this->routeMiddleware[$routeKey] ?? []));

        if ($routeMiddleware !== []) {
            $registrar->middleware(middleware: $routeMiddleware);
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

            $prefix .= '/' . trim(string: (string) $groupPrefix, characters: '/');
        }

        $qualifiedPath = trim(string: $prefix . '/' . ltrim(string: $path, characters: '/'), characters: '/');

        return '/' . $qualifiedPath;
    }

    private function routeKey(string $method, string $path) : string
    {
        return $method . ' ' . $path;
    }

    /**
 * @throws InvalidArgumentException
 */
private function registerWithDsl(string $method, string $path, callable|array|string $handler) : Registrar
    {
        return match ($method) {
            'GET'     => $this->router->get(path: $path, action: $handler),
            'POST'    => $this->router->post(path: $path, action: $handler),
            'PUT'     => $this->router->put(path: $path, action: $handler),
            'PATCH'   => $this->router->patch(path: $path, action: $handler),
            'DELETE'  => $this->router->delete(path: $path, action: $handler),
            'OPTIONS' => $this->router->options(path: $path, action: $handler),
            'HEAD'    => $this->router->head(path: $path, action: $handler),
            default   => throw new InvalidArgumentException(message: sprintf("Unsupported HTTP method '%s'.", $method)),
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
     * Assign middleware to a specific route.
     */
    public function middleware(string $routeKey, MiddlewareInterface|array $middleware) : self
    {
        $middlewareArray                  = is_array(value: $middleware) ? $middleware : [$middleware];
        $this->routeMiddleware[$routeKey] = array_merge(
            $this->routeMiddleware[$routeKey] ?? [],
            $middlewareArray,
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
