<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Configuration;

use Avax\Components\HTTP\Dispatcher\ControllerDispatcher;
use Avax\Components\HTTP\Middleware\MiddlewareInterface;
use Avax\Components\HTTP\Response\ResponseFactory;
use Avax\Components\HTTP\Router\RouterInterface;
use Avax\Components\HTTP\Router\RouterRuntimeInterface;
use Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Files\RouteRegistrarProxy;
use Avax\Components\HTTP\System\Capabilities\Kernel\AppKernel;
use InvalidArgumentException;
use LogicException;

/**
 * Router Bootstrapper - Route Registration and Middleware Configuration
 * Migrated from the HTTP root.
 */
final class RouterBootstrapper
{
    public array                        $globalMiddleware
        = [] {
            get {
                return $this->globalMiddleware;
            }
        }
    private RouterInterface             $router;
    private RouterRuntimeInterface|null $runtimeRouter;
    private array                       $routeMiddleware
        = [] {
            get {
                return $this->routeMiddleware;
            }
        }

    private array $middlewareGroups = [];
    private array $activeMiddleware = [];
    private array $groupPrefixes    = [];
    private array $routes           = [];

    public function __construct(RouterInterface $router, RouterRuntimeInterface|null $runtimeRouter = null)
    {
        $this->router        = $router;
        $this->runtimeRouter = $runtimeRouter ?? ($router instanceof RouterRuntimeInterface ? $router : null);
    }

    public function match(array $methods, string $path, callable|array|string $handler) : self
    {
        foreach ($methods as $method) {
            $this->route($method, $path, $handler);
        }

        return $this;
    }

    public function route(string $method, string $path, callable|array|string $handler) : self
    {
        $method          = strtoupper($method);
        $fullPath        = $this->qualifyPath($path);
        $routeKey        = $this->routeKey($method, $fullPath);
        $proxy           = $this->registerWithDsl($method, $fullPath, $handler);
        $routeMiddleware = array_values(array_merge(
                                            $this->activeMiddleware,
                                            $this->routeMiddleware[$routeKey] ?? []
                                        ));

        if ($routeMiddleware !== []) {
            $proxy->middleware($routeMiddleware);
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
            if ($groupPrefix === '') continue;
            $prefix .= '/' . trim($groupPrefix, '/');
        }
        $qualifiedPath = trim($prefix . '/' . ltrim($path, '/'), '/');

        return '/' . $qualifiedPath;
    }

    private function routeKey(string $method, string $path) : string
    {
        return $method . ' ' . $path;
    }

    private function registerWithDsl(string $method, string $path, callable|array|string $handler) : RouteRegistrarProxy
    {
        return match ($method) {
            'GET'     => $this->router->get($path, $handler),
            'POST'    => $this->router->post($path, $handler),
            'PUT'     => $this->router->put($path, $handler),
            'PATCH'   => $this->router->patch($path, $handler),
            'DELETE'  => $this->router->delete($path, $handler),
            'OPTIONS' => $this->router->options($path, $handler),
            'HEAD'    => $this->router->head($path, $handler),
            default   => throw new InvalidArgumentException("Unsupported HTTP method '{$method}'."),
        };
    }

    public function get(string $path, callable|array|string $handler) : self { return $this->route('GET', $path, $handler); }

    public function post(string $path, callable|array|string $handler) : self { return $this->route('POST', $path, $handler); }

    public function put(string $path, callable|array|string $handler) : self { return $this->route('PUT', $path, $handler); }

    public function patch(string $path, callable|array|string $handler) : self { return $this->route('PATCH', $path, $handler); }

    public function delete(string $path, callable|array|string $handler) : self { return $this->route('DELETE', $path, $handler); }

    public function middleware(string $routeKey, MiddlewareInterface|array $middleware) : self
    {
        $middlewareArray                  = is_array($middleware) ? $middleware : [$middleware];
        $this->routeMiddleware[$routeKey] = array_merge($this->routeMiddleware[$routeKey] ?? [], $middlewareArray);

        return $this;
    }

    public function group(callable $routes, array|null $middleware = null, string $prefix = '') : self
    {
        $middleware         ??= [];
        $previousMiddleware = $this->activeMiddleware;
        $previousPrefixes   = $this->groupPrefixes;

        $this->activeMiddleware = array_values(array_merge($this->activeMiddleware, $middleware));
        $this->groupPrefixes[]  = $prefix;

        $routes($this);

        $this->activeMiddleware = $previousMiddleware;
        $this->groupPrefixes    = $previousPrefixes;

        return $this;
    }

    public function middlewareGroup(string $name, array $middleware) : self
    {
        $this->middlewareGroups[$name] = $middleware;

        return $this;
    }

    public function useGroup(string $name) : self
    {
        if (! isset($this->middlewareGroups[$name])) {
            throw new InvalidArgumentException("Middleware group '{$name}' not defined.");
        }
        $this->activeMiddleware = array_values(array_merge($this->activeMiddleware, $this->middlewareGroups[$name]));

        return $this;
    }

    public function globalMiddleware(array $middleware) : self
    {
        $this->globalMiddleware = $middleware;

        return $this;
    }

    public function getRoutes() : array
    {
        return array_values($this->routes);
    }

    public function createApp(ControllerDispatcher $dispatcher, ResponseFactory $responseFactory) : AppKernel
    {
        $router = $this->bootstrap();

        return new AppKernel($router, $responseFactory, $this->globalMiddleware);
    }

    public function bootstrap() : RouterRuntimeInterface
    {
        if ($this->runtimeRouter === null) {
            throw new LogicException('RouterBootstrapper requires a runtime router instance to build an AppKernel.');
        }

        return $this->runtimeRouter;
    }
}
