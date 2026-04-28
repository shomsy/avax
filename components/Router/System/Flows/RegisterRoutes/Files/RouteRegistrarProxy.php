<?php

declare(strict_types=1);

namespace Avax\Components\Router\System\Flows\RegisterRoutes\Files;

use Avax\Components\Router\System\Capabilities\RouteDefinition\RouteDefinition;
use Avax\Components\Router\System\Flows\RegisterRoutes\Definitions\RouteBuilder;
use Avax\Components\Router\System\Flows\RegisterRoutes\Definitions\RouteRegistry;
use Avax\Components\Router\System\Flows\ResolveRequest\HttpRequestRouter;

/**
 * Proxy that wraps a RouteBuilder and lazily registers the route.
 */
final class RouteRegistrarProxy
{
    public function __construct(
        private readonly HttpRequestRouter|null $router,
        private readonly RouteBuilder           $builder,
        private readonly RouteRegistry|null     $registry,
        private bool                            $registered = false
    ) {}

    public function name(string $name) : self
    {
        $this->builder->name($name);
        $this->register();

        return $this;
    }

    public function register() : self
    {
        if (! $this->registered && $this->registry !== null) {
            $this->registry->add($this->builder);
            $this->registered = true;
        }

        return $this;
    }

    public function build() : RouteDefinition { return $this->builder->build(); }

    public function where(string $param, string $pattern) : self
    {
        $this->builder->where($param, $pattern);

        return $this;
    }

    public function defaults(array $defaults) : self
    {
        $this->builder->defaults($defaults);

        return $this;
    }

    public function middleware(array $middleware) : self
    {
        $this->builder->middleware($middleware);

        return $this;
    }

    public function action(callable|array|string $action) : self
    {
        $this->builder->action($action);

        return $this;
    }

    public function __destruct() { $this->register(); }
}
