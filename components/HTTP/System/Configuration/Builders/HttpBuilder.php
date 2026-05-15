<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Configuration\Builders;

use Avax\Components\Application\Container\System\Capabilities\ResolveCallable\ResolveCallable;
use Avax\Components\HTTP\Router\System\Capabilities\ErrorResponseBuilding\BuildErrorResponse;
use Avax\Components\HTTP\Router\System\Capabilities\MiddlewarePipeline\BuildPipeline;
use Avax\Components\HTTP\Router\System\Capabilities\ResponseNormalization\NormalizeControllerResult;
use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteCollection;
use Avax\Components\HTTP\Router\System\Capabilities\RouteExecution\InvokeRouteAction;
use Avax\Components\HTTP\Router\System\Capabilities\UrlBuilding\SubstituteRouteParameters;
use Avax\Components\HTTP\Router\System\Flows\MatchRoute\MatchRoute;
use Avax\Components\HTTP\Router\System\PublicSurface\Router;
use Avax\Components\HTTP\System\Capabilities\MiddlewarePipeline\MiddlewarePipeline;
use Avax\Components\HTTP\System\PublicSurface\Http;

final class HttpBuilder
{
    public function build() : Http
    {
        $routeCollection    = new RouteCollection();
        $matchRoute         = new MatchRoute();
        $resolveCallable    = new ResolveCallable();
        $responseNormalizer = new NormalizeControllerResult();

        return new Http(
            new Router(
                routeCollection  : $routeCollection,
                matchRoute       : $matchRoute,
                pipelineBuilder  : new BuildPipeline($resolveCallable),
                errorResponse    : new BuildErrorResponse(),
                urlBuilder       : new SubstituteRouteParameters(),
                invokeRouteAction: new InvokeRouteAction($responseNormalizer),
            ),
            new MiddlewarePipeline(),
        );
    }
}
