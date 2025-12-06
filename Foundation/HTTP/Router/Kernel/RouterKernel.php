<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Kernel;

use Avax\HTTP\Request\Request;
use Avax\HTTP\Router\Routing\HttpRequestRouter;
use Avax\HTTP\Router\Routing\RouteDefinition;
use Avax\HTTP\Router\Routing\RoutePipelineFactory;
use Avax\HTTP\Router\Support\HeadRequestFallback;
use Psr\Http\Message\ResponseInterface;

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
    /**
     * Constructor initializes the core dependencies for the routing kernel.
     *
     * @param HttpRequestRouter    $httpRequestRouter   Responsible for resolving HTTP routes.
     * @param RoutePipelineFactory $pipelineFactory     Creates pipelines to process route handling.
     * @param HeadRequestFallback  $headRequestFallback Provides fallback processing for HEAD requests.
     */
    public function __construct(
        private HttpRequestRouter    $httpRequestRouter,
        private RoutePipelineFactory $pipelineFactory,
        private HeadRequestFallback  $headRequestFallback
    ) {}

    /**
     * Handles an incoming HTTP request by resolving the corresponding route,
     * applying middleware, and processing the pipeline.
     *
     * @param Request $request The HTTP request to be processed.
     *
     * @return ResponseInterface The HTTP response produced after processing.
     *
     * @throws \ReflectionException Signals issues with runtime reflection in the pipeline processing.
     * @throws \Psr\Container\ContainerExceptionInterface Indicates a container-related error occurred.
     * @throws \Psr\Container\NotFoundExceptionInterface Indicates a requested service was not found.
     */
    public function handle(Request $request) : ResponseInterface
    {
        // Apply fallback logic for HEAD requests, converting them to GET if needed.
        $request = $this->headRequestFallback->resolve($request);

        // Resolve the current request into a matching route definition.
        $route = $this->httpRequestRouter->resolve($request);

        // Inject route parameters and defaults into the request as attributes.
        $request = $this->injectRouteAttributes($request, $route);

        // Create a middleware pipeline based on the resolved route.
        $pipeline = $this->pipelineFactory->create($route);

        // Process the pipeline and dispatch the final response.
        return $pipeline->dispatch($request);
    }

    /**
     * Injects route parameters and default values into the request as attributes.
     *
     * This method ensures the request contains all the attributes defined
     * in the route and sets default values where attributes are missing.
     *
     * @param Request         $request The current HTTP request.
     * @param RouteDefinition $route   The route definition containing parameters and defaults.
     *
     * @return Request A new request object with the injected attributes.
     */
    private function injectRouteAttributes(Request $request, RouteDefinition $route) : Request
    {
        // Inject route parameters as attributes into the request.
        foreach ($route->parameters as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }

        // Inject default values for attributes that are not already set in the request.
        foreach ($route->defaults as $key => $value) {
            if ($request->getAttribute($key) === null) {
                $request = $request->withAttribute($key, $value);
            }
        }

        // Return a modified request containing all the injected attributes.
        return $request;
    }
}