<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing;

use Avax\HTTP\Router\Support\RouteRegistry;

/**
 * Encapsulates DSL helpers that define and buffer routes prior to bootstrap.
 */
final readonly class RouterRegistrar
{
    public function __construct(
        private RouteRegistry     $registry,
        private HttpRequestRouter $httpRequestRouter
    ) {}

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

    // Fallback method removed - fallbacks are handled exclusively through FallbackManager

    /**
     * @internal For cache loader use only.
     */
    public function registerRouteFromCache(RouteDefinition $definition) : void
    {
        $this->httpRequestRouter->add(route: $definition);
    }
}