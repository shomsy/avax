<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Kernel;

use Avax\HTTP\Request\Request;
use Avax\HTTP\Router\Routing\Exceptions\MethodNotAllowedException;
use Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException;
use Avax\HTTP\Router\Routing\Exceptions\RouteNotFoundException;
use Avax\HTTP\Router\Routing\HttpRequestRouter;
use Avax\HTTP\Router\Routing\RouteExecutor;
use Avax\HTTP\Router\Routing\RoutePipelineFactory;
use Avax\HTTP\Router\Support\HeadRequestFallback;
use Avax\HTTP\Router\Support\RouteRequestInjector;
use Avax\HTTP\Router\Tracing\RouterTrace;
use Avax\HTTP\Router\Validation\Exceptions\InvalidConstraintException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use ReflectionException;
use Throwable;

/**
 * Class RouterKernel
 *
 * This class is the main entry point of the router kernel, providing a clean,
 * domain-oriented design for HTTP request handling. It resolves routes,
 * applies middleware, and dispatches the request pipeline to produce an HTTP response.
 *
 * The class is marked as `readonly` to ensure that injected dependencies
 * and their state remain immutable, strictly adhering to DDD principles.
 */
final readonly class RouterKernel
{
    private RouterTrace|null     $trace;
    private RouteExecutor        $routeExecutor;
    private HeadRequestFallback  $headRequestFallback;
    private RoutePipelineFactory $pipelineFactory;
    private HttpRequestRouter    $httpRequestRouter;

    /**
     * Constructor initializes the core dependencies for the routing kernel.
     *
     * @param HttpRequestRouter    $httpRequestRouter   Responsible for resolving HTTP routes.
     * @param RoutePipelineFactory $pipelineFactory     Creates pipelines to process route handling.
     * @param HeadRequestFallback  $headRequestFallback Provides fallback processing for HEAD requests.
     * @param RouterTrace|null     $trace               Optional trace instance for debugging and profiling.
     */
    public function __construct(
        HttpRequestRouter    $httpRequestRouter,
        RoutePipelineFactory $pipelineFactory,
        HeadRequestFallback  $headRequestFallback,
        RouteExecutor        $routeExecutor,
        RouterTrace|null     $trace = null
    )
    {
        $this->httpRequestRouter   = $httpRequestRouter;
        $this->pipelineFactory     = $pipelineFactory;
        $this->headRequestFallback = $headRequestFallback;
        $this->routeExecutor       = $routeExecutor;
        $this->trace               = $trace;
    }

    /**
     * Handles an incoming HTTP request by resolving the corresponding route,
     * applying middleware, and processing the pipeline.
     *
     * Provides comprehensive tracing for debugging and profiling.
     *
     * @param Request $request The HTTP request to be processed.
     *
     * @return ResponseInterface The HTTP response produced after processing.
     *
     * @throws ContainerExceptionInterface Indicates a container-related error occurred.
     * @throws InvalidConstraintException
     * @throws NotFoundExceptionInterface Indicates a requested service was not found.
     * @throws ReflectionException Signals issues with runtime reflection in the pipeline processing.
     * @throws Throwable
     * @throws ReservedRouteNameException
     */
    public function handle(Request $request) : ResponseInterface
    {
        $startTime = microtime(true);
        $method    = $request->getMethod();
        $path      = $request->getUri()->getPath();

        // Trace: ServerRequest resolution started
        $this->trace?->log(event: 'kernel.resolve.start', context: [
            'method' => $method,
            'path'   => $path,
            'host'   => $request->getUri()->getHost(),
        ]);

        try {
            // Apply fallback logic for HEAD requests, converting them to GET if needed.
            $request = $this->headRequestFallback->resolve(request: $request);

            // Resolve the current request into structured resolution context.
            $resolutionContext = $this->httpRequestRouter->resolve(request: $request);

            // Extract route from resolution context
            $route = $resolutionContext->route;

            // Trace: Route matched successfully
            $this->trace?->log(event: 'kernel.route.matched', context: [
                'route'    => $route->name ?? $route->path,
                'method'   => $route->method,
                'path'     => $route->path,
                'domain'   => $route->domain,
                'duration' => round((microtime(true) - $startTime) * 1000, 2) . 'ms',
            ]);

            // Inject route parameters from resolution context into the request as attributes.
            $request = RouteRequestInjector::injectWithContext(
                request   : $request,
                route     : $route,
                parameters: $resolutionContext->parameters
            );

            // Create a middleware pipeline based on the resolved route.
            $pipeline = $this->pipelineFactory->create(route: $route);

            // Process the pipeline and dispatch the final response.
            $response = $pipeline->dispatch(request: $request);

            // Trace: ServerRequest handled successfully
            $this->trace?->log(event: 'kernel.request.complete', context: [
                'route'    => $route->name ?? $route->path,
                'status'   => $response->statusCode,
                'duration' => round((microtime(true) - $startTime) * 1000, 2) . 'ms',
            ]);

            return $response;

        } catch (Throwable $exception) {
            // Trace: ServerRequest failed with fallback or error
            $this->trace?->log(event: 'kernel.request.failed', context: [
                'method'    => $method,
                'path'      => $path,
                'exception' => get_class($exception),
                'message'   => $exception->getMessage(),
                'duration'  => round((microtime(true) - $startTime) * 1000, 2) . 'ms',
            ]);

            // Check if this is a routing exception that should trigger fallback
            if ($exception instanceof RouteNotFoundException ||
                $exception instanceof MethodNotAllowedException) {

                $this->trace?->log(event: 'kernel.fallback.triggered', context: [
                    'reason'  => get_class($exception),
                    'message' => $exception->getMessage(),
                ]);

                // Re-throw to let Router handle fallback
                throw $exception;
            }

            // Re-throw other exceptions
            throw $exception;
        }
    }
}