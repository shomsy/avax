<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\HandleIncomingHttp;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;
use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteCollection;
use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteMethod;
use Avax\Components\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;
use Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Files\Registrar;
use Avax\Components\HTTP\Router\System\PublicSurface\RouterInterface;
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
        return $this->register(method: RouteMethod::GET, path: $path, action: $action);
    }

    public function post(string $path, mixed $action): Registrar
    {
        return $this->register(method: RouteMethod::POST, path: $path, action: $action);
    }

    public function put(string $path, mixed $action): Registrar
    {
        return $this->register(method: RouteMethod::PUT, path: $path, action: $action);
    }

    public function patch(string $path, mixed $action): Registrar
    {
        return $this->register(method: RouteMethod::PATCH, path: $path, action: $action);
    }

    public function delete(string $path, mixed $action): Registrar
    {
        return $this->register(method: RouteMethod::DELETE, path: $path, action: $action);
    }

    public function options(string $path, mixed $action): Registrar
    {
        return $this->register(method: RouteMethod::OPTIONS, path: $path, action: $action);
    }

    public function head(string $path, mixed $action): Registrar
    {
        return $this->register(method: RouteMethod::HEAD, path: $path, action: $action);
    }

    public function any(string $path, mixed $action): Registrar
    {
        return $this->register(method: RouteMethod::GET, path: $path, action: $action);
    }

    /**
     * @return list<Registrar>
     */
    public function anyExpanded(string $path, mixed $action): array
    {
        $proxies = [];

        foreach (RouteMethod::cases() as $method) {
            $proxies[] = $this->register(method: $method, path: $path, action: $action);
        }

        return $proxies;
    }

    public function group(string $prefix, callable $groupFn, string|array|callable|null $middleware = null) : void
    {
        // FrameworkRouteRegistrar is a configuration-only registrar.
        // Route groups are handled by the actual Router at dispatch time.
        // This method exists only to satisfy the interface contract.
    }

    /**
     * @throws RuntimeException Always, as URL generation is not supported
     */
    public function url(string $name, array $parameters = [], bool $absolute = false) : string
    {
        throw new RuntimeException('URL generation is not supported through FrameworkRouteRegistrar.');
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
            $method = $definition->method()->value;

            $routesByMethod[$method] ??= [];
            $routesByMethod[$method][] = $definition;
        }

        return new RegisteredHttpRoutes(
            routesByMethod: $routesByMethod,
            fallback      : $this->fallback,
        );
    }

    private function register(RouteMethod $method, string $path, mixed $action): Registrar
    {
        $definition = new RouteDefinition(method: $method, uri: $path, action: $action);

        $this->routes[] = $definition;

        return new Registrar(new RouteCollection(), $definition);
    }

    /**
     * @throws RuntimeException Always, as dispatching is not supported
     */
    public function dispatch(RequestInterface $request): ResponseInterface
    {
        throw new RuntimeException('FrameworkRouteRegistrar is purely for configuration and does not support dispatching directly.');
    }
}
