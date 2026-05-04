<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\HandleIncomingHttp;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;
use Avax\Components\HTTP\Router\System\PublicSurface\RouterInterface;
use Avax\Components\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;
use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteMethod;
use Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Files\Registrar;
use Closure;
use RuntimeException;

final class FrameworkRouteRegistrar implements RouterInterface
{
    /** @var Closure|array<mixed>|string|null */
    private Closure|array|string|null $fallback = null;

    /** @var list<RouteDefinition> */
    private array $routes = [];

    public function __construct()
    {
    }

    public function get(string $path, mixed $action): Registrar
    {
        return $this->register(method: 'GET', path: $path, action: $action);
    }

    public function post(string $path, mixed $action): Registrar
    {
        return $this->register(method: 'POST', path: $path, action: $action);
    }

    public function put(string $path, mixed $action): Registrar
    {
        return $this->register(method: 'PUT', path: $path, action: $action);
    }

    public function patch(string $path, mixed $action): Registrar
    {
        return $this->register(method: 'PATCH', path: $path, action: $action);
    }

    public function delete(string $path, mixed $action): Registrar
    {
        return $this->register(method: 'DELETE', path: $path, action: $action);
    }

    public function options(string $path, mixed $action): Registrar
    {
        return $this->register(method: 'OPTIONS', path: $path, action: $action);
    }

    public function head(string $path, mixed $action): Registrar
    {
        return $this->register(method: 'HEAD', path: $path, action: $action);
    }

    public function any(string $path, mixed $action): Registrar
    {
        return $this->register(method: 'ANY', path: $path, action: $action);
    }

    /**
     * @return list<Registrar>
     */
    public function anyExpanded(string $path, mixed $action): array
    {
        $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'HEAD'];
        $proxies = [];

        foreach ($methods as $method) {
            $proxies[] = $this->register(method: $method, path: $path, action: $action);
        }

        return $proxies;
    }

    public function fallback(mixed $handler): void
    {
        $this->fallback = is_string(value: $handler) || is_array(value: $handler)
            ? $handler
            : Closure::fromCallable(callback: $handler);
    }

    public function collectRoutes(): RegisteredHttpRoutes
    {
        $routesByMethod = [];

        foreach ($this->routes as $definition) {
            $method = strtoupper(string: $definition->method()->toString());

            $routesByMethod[$method] ??= [];
            $routesByMethod[$method][] = $definition;
        }

        return new RegisteredHttpRoutes(
            routesByMethod: $routesByMethod,
            fallback      : $this->fallback,
        );
    }

    private function register(string $method, string $path, mixed $action): Registrar
    {
        $definition = new RouteDefinition(method: new RouteMethod($method), uri: $path, action: $action);

        $this->routes[] = $definition;

        return new Registrar(route: $definition);
    }

    public function dispatch(RequestInterface $request) : ResponseInterface
    {
        throw new RuntimeException('FrameworkRouteRegistrar is purely for configuration and does not support dispatching directly.');
    }
}
