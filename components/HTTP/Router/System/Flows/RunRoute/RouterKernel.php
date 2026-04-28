<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Flows\RunRoute;

use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Avax\Components\HTTP\Router\System\Capabilities\RouterTrace\RouterTrace;
use Avax\Components\HTTP\Router\System\Flows\ResolveRequest\HeadRequests\ApplyHeadRequestFallback;
use Avax\Components\HTTP\Router\System\Flows\ResolveRequest\HttpRequestRouter;
use Avax\Components\HTTP\Router\System\Flows\ResolveRequest\Request\RouteRequestInjector;
use Avax\Components\HTTP\Router\System\Flows\RunRoute\Dispatch\RouteExecutor;
use Avax\Components\HTTP\Router\System\Flows\RunRoute\Pipeline\RoutePipelineFactory;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Main entry point for the Router kernel.
 */
final readonly class RouterKernel
{
    public function __construct(
        private HttpRequestRouter        $httpRequestRouter,
        private RoutePipelineFactory     $pipelineFactory,
        private ApplyHeadRequestFallback $headRequestFallback,
        private RouteExecutor            $routeExecutor,
        private RouterTrace|null         $trace = null
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(ServerRequest $request) : ResponseInterface
    {
        $startTime = microtime(as_float: true);

        try {
            $request           = $this->headRequestFallback->resolve(request: $request);
            $resolutionContext = $this->httpRequestRouter->resolve(request: $request);
            $route             = $resolutionContext->route;

            $request = RouteRequestInjector::injectExtractedParameters(
                request   : $request,
                defaults  : $route->defaults,
                parameters: $resolutionContext->parameters
            );

            $pipeline = $this->pipelineFactory->create(route: $route);
            $response = $pipeline->dispatch(request: $request);

            return $response;

        } catch (Throwable $exception) {
            throw $exception;
        }
    }
}
