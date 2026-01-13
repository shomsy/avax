<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Bootstrap;

use Avax\HTTP\Router\RouterInterface;
use Avax\HTTP\Router\Routing\HttpRequestRouter;
use Avax\HTTP\Router\Routing\RouteGroupStack;
use Avax\HTTP\Router\Support\RouteCollector;
use Avax\HTTP\Router\Support\RouteRegistry;

/**
 * Route loader that avoids static collectors.
 *
 * @see docs/Http/RouteRegistrar.md#quick-summary
 */
final readonly class RouteRegistrar
{
    public function __construct(
        private RouterInterface   $dslRouter,
        private HttpRequestRouter $httpRouter,
        private RouteGroupStack   $groupStack,
        private RouteRegistry     $routeRegistry
    ) {}

    /**
     * Load routes from file with registry integration.
     *
     * @see docs/Http/RouteRegistrar.md#method-load
     * @throws \Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException
     */
    public function load(string $path, string $cacheDir) : void
    {
        if (is_file($path)) {
            $snapshot  = $this->groupStack->snapshot();
            $router    = $this->httpRouter; // expose low-level router for route files
            $dslRouter = $this->dslRouter; // expose DSL if needed
            require $path;

            // Flush collected routes and register them with the router
            foreach ($this->routeRegistry->flush() as $routeBuilder) {
                $definition = $routeBuilder->build();
                $this->httpRouter->add(route: $definition);
            }

            // Note: Fallback handling is now done exclusively through FallbackManager
            // The registry fallback is used only during DSL execution

            $this->groupStack->restore(stack: $snapshot);
        }
    }
}