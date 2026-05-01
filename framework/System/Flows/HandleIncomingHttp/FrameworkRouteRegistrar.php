<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\HandleIncomingHttp;

use Avax\Components\HTTP\Router\HttpMethod;
use Avax\Components\HTTP\Router\RouterInterface;
use Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Definitions\RouteBuilder;
use Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Definitions\RouteRegistry;
use Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Files\RouteRegistrarProxy;
use Closure;

final class FrameworkRouteRegistrar implements RouterInterface
{
    private Closure|array|string|null $fallback = null;

    public function __construct(private readonly RouteRegistry $routeRegistry = new RouteRegistry())
    {
    }

    public function get(string $path, callable|array|string $action): RouteRegistrarProxy
    {
        return $this->register(method: HttpMethod::GET->value, path: $path, action: $action);
    }

    public function post(string $path, callable|array|string $action): RouteRegistrarProxy
    {
        return $this->register(method: HttpMethod::POST->value, path: $path, action: $action);
    }

    public function put(string $path, callable|array|string $action): RouteRegistrarProxy
    {
        return $this->register(method: HttpMethod::PUT->value, path: $path, action: $action);
    }

    public function patch(string $path, callable|array|string $action): RouteRegistrarProxy
    {
        return $this->register(method: HttpMethod::PATCH->value, path: $path, action: $action);
    }

    public function delete(string $path, callable|array|string $action): RouteRegistrarProxy
    {
        return $this->register(method: HttpMethod::DELETE->value, path: $path, action: $action);
    }

    public function options(string $path, callable|array|string $action): RouteRegistrarProxy
    {
        return $this->register(method: HttpMethod::OPTIONS->value, path: $path, action: $action);
    }

    public function head(string $path, callable|array|string $action): RouteRegistrarProxy
    {
        return $this->register(method: HttpMethod::HEAD->value, path: $path, action: $action);
    }

    public function any(string $path, callable|array|string $action): RouteRegistrarProxy
    {
        return $this->register(method: HttpMethod::ANY->value, path: $path, action: $action);
    }

    public function anyExpanded(string $path, callable|array|string $action): array
    {
        $proxies = [];

        foreach (HttpMethod::cases() as $method) {
            if ($method === HttpMethod::ANY) {
                continue;
            }

            $proxies[] = $this->register(method: $method->value, path: $path, action: $action);
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

        foreach ($this->routeRegistry->flush() as $builder) {
            $definition = $builder->build();
            $method     = strtoupper(string: $definition->method);

            $routesByMethod[$method] ??= [];
            $routesByMethod[$method][] = $definition;
        }

        return new RegisteredHttpRoutes(
            routesByMethod: $routesByMethod,
            fallback      : $this->fallback,
        );
    }

    private function register(string $method, string $path, callable|array|string $action): RouteRegistrarProxy
    {
        $builder = RouteBuilder::make(method: $method, path: $path);
        $builder->action(action: $action);

        return new RouteRegistrarProxy(
            router    : null,
            builder   : $builder,
            registry  : $this->routeRegistry,
            registered: false,
        );
    }
}
