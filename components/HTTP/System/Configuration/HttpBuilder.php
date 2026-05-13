<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ResolveCallable\ResolveCallable;
use Avax\Components\HTTP\Middleware\System\Capabilities\Pipeline\MiddlewarePipeline;
use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteCollection;
use Avax\Components\HTTP\Router\System\Flows\MatchRoute\MatchRoute;
use Avax\Components\HTTP\Router\System\PublicSurface\Router;
use Avax\Components\HTTP\System\PublicSurface\Http;

final class HttpBuilder
{
    public function build() : Http
    {
        $routeCollection = new RouteCollection();
        $matchRoute      = new MatchRoute();
        $resolveCallable = new ResolveCallable();

        return new Http(
            new Router($resolveCallable, $routeCollection, $matchRoute),
            new MiddlewarePipeline(),
        );
    }
}
