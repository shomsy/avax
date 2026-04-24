<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\System\Flows\RegisterRoutes\Definitions;

use Avax\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;
use Avax\HTTP\Router\System\Flows\RegisterRoutes\Files\RouteRegistrarProxy;
use Avax\HTTP\Router\System\Flows\ResolveRequest\HttpRequestRouter;
use Avax\HTTP\Router\System\Foundation\Exceptions\DuplicateRouteException;

/**
 * Encapsulates DSL helpers that define and buffer routes prior to bootstrap.
 */
final readonly class RouterRegistrar
{
    private HttpRequestRouter $httpRequestRouter;
    private RouteRegistry     $registry;

    public function __construct(
        RouteRegistry     $registry,
        HttpRequestRouter $httpRequestRouter
    )
    {
        $this->registry          = $registry;
        $this->httpRequestRouter = $httpRequestRouter;
    }

    public function register(string $method, string $path, callable|array|string $action) : RouteRegistrarProxy
    {
        $builder = RouteBuilder::make(method: $method, path: $path);
        $builder->action(action: $action);

        $this->registry->add(builder: $builder);

        return new RouteRegistrarProxy(
            router  : $this->httpRequestRouter,
            builder : $builder,
            registry: $this->registry
        );
    }

    // Fallback method removed - fallbacks are handled exclusively through RegisteredFallback

    /**
     * @throws DuplicateRouteException
     * @internal For cache loader use only.
     */
    public function registerRouteFromCache(RouteDefinition $definition) : void
    {
        $this->httpRequestRouter->add(route: $definition);
    }
}
