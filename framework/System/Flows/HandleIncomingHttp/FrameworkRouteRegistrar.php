<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\HandleIncomingHttp;

use Avax\Components\HTTP\Router\System\PublicSurface\RouterInterface;
use Avax\Components\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;
use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteMethod;
use Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Definitions\RouteRegistry;
use Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Files\Registrar;
use Closure;

final class FrameworkRouteRegistrar implements RouterInterface
{
    private Closure|array|string|null $fallback = null;

    public function __construct(private readonly RouteRegistry $routeRegistry = new RouteRegistry())
    {
    }

    public function get(string $path, callable|array|string $action): Registrar
    {
        return $this->register(method: 'GET', path: $path, action: $action);
    }

    public function post(string $path, callable|array|string $action): Registrar
    {
        return $this->register(method: 'POST', path: $path, action: $action);
    }

    public function put(string $path, callable|array|string $action): Registrar
    {
        return $this->register(method: 'PUT', path: $path, action: $action);
    }

    public function patch(string $path, callable|array|string $action): Registrar
    {
        return $this->register(method: 'PATCH', path: $path, action: $action);
    }

    public function delete(string $path, callable|array|string $action): Registrar
    {
        return $this->register(method: 'DELETE', path: $path, action: $action);
    }

    public function options(string $path, callable|array|string $action): Registrar
    {
        return $this->register(method: 'OPTIONS', path: $path, action: $action);
    }

    public function head(string $path, callable|array|string $action): Registrar
    {
        return $this->register(method: 'HEAD', path: $path, action: $action);
    }

    public function any(string $path, callable|array|string $action): Registrar
    {
        return $this->register(method: 'ANY', path: $path, action: $action);
    }

    public function anyExpanded(string $path, callable|array|string $action): array
    {
        $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'HEAD'];
        $proxies = [];

        foreach ($methods as $method) {
            $proxies[] = $this->register(method: $method, path: $path, action: $action);
        }

        return $proxies;
    }

    public function fallback(callable|array|string $handler): void
    {
        $this->fallback = is_string($handler) || is_array($handler)
            ? $handler
            : Closure::fromCallable(callback: $handler);

        $this->routeRegistry->setFallback(fallback: $this->fallback);
    }

    public function collectRoutes(): RegisteredHttpRoutes
    {
        $routesByMethod = [];

        foreach ($this->routeRegistry->flush() as $definition) {
            $method = strtoupper(string: (string) $definition->method);

            $routesByMethod[$method] ??= [];
            $routesByMethod[$method][] = $definition;
        }

        return new RegisteredHttpRoutes(
            routesByMethod: $routesByMethod,
            fallback      : $this->fallback,
        );
    }

    private function register(string $method, string $path, callable|array|string $action): Registrar
    {
        $definition = new RouteDefinition(method: new RouteMethod($method), path: $path, action: $action);

        $this->routeRegistry->add(routeDefinition: $definition);

        return new Registrar(route: $definition);
    }
}

