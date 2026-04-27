<?php

declare(strict_types=1);

namespace Avax\Components\Router\System\Flows\RegisterRoutes\Files;

use Avax\Components\Router\System\Flows\RegisterRoutes\Definitions\RouteRegistry;
use Avax\Components\Router\System\Flows\RegisterRoutes\Groups\RouteGroupFrames;
use Avax\Components\Router\System\Flows\ResolveRequest\HttpRequestRouter;
use Avax\Components\Router\System\PublicSurface\RouterInterface;

/**
 * Route loader that avoids static collectors and manages group stacks.
 */
final readonly class RouteFileRegistrar
{
    public function __construct(
        private RouterInterface   $dslRouter,
        private HttpRequestRouter $httpRouter,
        private RouteGroupFrames  $groupStack,
        private RouteRegistry     $routeRegistry
    ) {}

    public function load(string $path, string $cacheDir = '') : void
    {
        if (is_file($path)) {
            $snapshot  = $this->groupStack->snapshot();
            $router    = $this->httpRouter;
            $dslRouter = $this->dslRouter;

            RouteCollector::scoped(
                closure : static function () use ($path, $router, $dslRouter) : void {
                    require $path;
                },
                router  : $this->httpRouter,
                registry: $this->routeRegistry
            );

            foreach ($this->routeRegistry->flush() as $routeBuilder) {
                $this->httpRouter->add($routeBuilder->build());
            }

            $this->groupStack->restore($snapshot);
        }
    }
}
