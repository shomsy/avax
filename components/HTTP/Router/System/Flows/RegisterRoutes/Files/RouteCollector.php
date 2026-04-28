<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Files;

use Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Definitions\RouteBuilder;
use Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Definitions\RouteRegistry;
use Avax\Components\HTTP\Router\System\Flows\ResolveRequest\HttpRequestRouter;
use Closure;
use LogicException;
use Throwable;

/**
 * RouteCollector is the bridge between route DSL execution and router registration.
 * uses instance-based collection to ensure thread safety and isolation.
 */
final class RouteCollector
{
    private static self|null $current  = null;
    private array            $routes   = [];
    private                  $fallback = null;

    public function __construct(
        private readonly HttpRequestRouter|null $router = null,
        private readonly RouteRegistry|null     $registry = null
    ) {}

    public static function scoped(
        Closure                $closure,
        HttpRequestRouter|null $router = null,
        RouteRegistry|null     $registry = null
    ) : self
    {
        $collector     = new self(router: $router, registry: $registry);
        $previous      = self::$current;
        self::$current = $collector;

        try {
            $closure($collector);

            return $collector;
        } catch (Throwable $exception) {
            $collector->clear();
            throw $exception;
        } finally {
            self::$current = $previous;
        }
    }

    public function clear() : void
    {
        $this->routes   = [];
        $this->fallback = null;
    }

    public static function current() : self
    {
        if (self::$current === null) {
            throw new LogicException('Route DSL functions must run inside a route registration scope.');
        }

        return self::$current;
    }

    public function addRouteBuilder(RouteBuilder $routeBuilder) : RouteRegistrarProxy
    {
        $this->routes[] = $routeBuilder;
        $registered     = false;
        if ($this->registry !== null) {
            $this->registry->add(builder: $routeBuilder);
            $registered = true;
        }

        return new RouteRegistrarProxy(
            router    : $this->router,
            builder   : $routeBuilder,
            registry  : $this->registry,
            registered: $registered
        );
    }

    public function flush() : array
    {
        $routes       = $this->routes;
        $this->routes = [];

        return $routes;
    }

    public function getFallback() : callable|null
    {
        if ($this->registry !== null && $this->registry->hasFallback()) {
            return $this->registry->fallback();
        }

        return $this->fallback;
    }

    public function setFallback(callable|array|string $fallback) : void
    {
        $this->fallback = $fallback;
        $this->registry?->setFallback(fallback: $fallback);
    }
}
