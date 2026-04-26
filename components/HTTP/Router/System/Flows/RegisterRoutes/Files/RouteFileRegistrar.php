<?php

declare(strict_types=1);

namespace components\HTTP\Router\System\Flows\RegisterRoutes\Files;

use components\HTTP\Router\RouterInterface;
use components\HTTP\Router\System\Flows\RegisterRoutes\Definitions\RouteRegistry;
use components\HTTP\Router\System\Flows\RegisterRoutes\Groups\RouteGroupFrames;
use components\HTTP\Router\System\Flows\ResolveRequest\HttpRequestRouter;
use components\HTTP\Router\System\Foundation\Exceptions\DuplicateRouteException;
use components\HTTP\Router\System\Foundation\Exceptions\ReservedRouteNameException;

/**
 * Route loader that avoids static collectors.
 *
 * @see docs/Http/RouteFileRegistrar.md#quick-summary
 */
final readonly class RouteFileRegistrar
{
    private RouteRegistry     $routeRegistry;
    private RouteGroupFrames  $groupStack;
    private HttpRequestRouter $httpRouter;
    private RouterInterface   $dslRouter;

    public function __construct(
        RouterInterface   $dslRouter,
        HttpRequestRouter $httpRouter,
        RouteGroupFrames  $groupStack,
        RouteRegistry     $routeRegistry
    )
    {
        $this->dslRouter     = $dslRouter;
        $this->httpRouter    = $httpRouter;
        $this->groupStack    = $groupStack;
        $this->routeRegistry = $routeRegistry;
    }

    /**
     * Load routes from file with registry integration.
     *
     * @see docs/Http/RouteFileRegistrar.md#method-load
     * @throws ReservedRouteNameException
     * @throws DuplicateRouteException
     */
    public function load(string $path, string $cacheDir) : void
    {
        if (is_file(filename: $path)) {
            $snapshot  = $this->groupStack->snapshot();
            $router    = $this->httpRouter; // expose low-level router for route files
            $dslRouter = $this->dslRouter; // expose DSL if needed
            RouteCollector::scoped(
                closure : static function () use ($path, $router, $dslRouter) : void {
                    require $path;
                },
                router  : $this->httpRouter,
                registry: $this->routeRegistry
            );

            // Flush collected routes and register them with the router
            foreach ($this->routeRegistry->flush() as $routeBuilder) {
                $definition = $routeBuilder->build();
                $this->httpRouter->add(route: $definition);
            }

            // Note: Fallback handling is now done exclusively through RegisteredFallback
            // The registry fallback is used only during DSL execution

            $this->groupStack->restore(stack: $snapshot);
        }
    }
}
