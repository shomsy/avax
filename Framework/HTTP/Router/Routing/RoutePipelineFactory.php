<?php

declare(strict_types=1);

namespace Gemini\HTTP\Router\Routing;

use Gemini\Container\Contracts\ContainerInterface;
use Gemini\HTTP\Dispatcher\ControllerDispatcher;
use Gemini\HTTP\Middleware\MiddlewareResolver;

/**
 * Factory class that constructs and initializes a complete route pipeline for dispatch.
 *
 * The `RoutePipelineFactory` is a fundamental part of request handling. It integrates
 * the resolved middleware, the routing definitions, and dependencies like the controller
 * dispatcher and IoC container to create a fully prepared route execution pipeline.
 */
final readonly class RoutePipelineFactory
{
    /**
     * Constructor for the RoutePipelineFactory.
     *
     * This method leverages the constructor promotion feature in PHP to simplify property initialization.
     * The dependencies required for constructing a route pipeline—IoC
     * container, controller dispatcher, and middleware resolver—are injected via the constructor.
     *
     * @param ContainerInterface   $container          The application container for managing services.
     * @param ControllerDispatcher $dispatcher         The dispatcher responsible for managing controller execution.
     * @param MiddlewareResolver   $middlewareResolver Middleware resolver for resolving middleware definitions.
     */
    public function __construct(
        private ContainerInterface   $container,
        private ControllerDispatcher $dispatcher,
        private MiddlewareResolver   $middlewareResolver,
    ) {}

    /**
     * Factory method for creating a fully resolved route pipeline.
     *
     * This method uses the provided route definition to fetch middleware, resolve their instances
     * using the MiddlewareResolver, and then constructs a new RoutePipeline. The pipeline
     * is configured with middleware and is ready for dispatch.
     *
     * @param RouteDefinition $route The route definition, containing route-specific information such as middleware.
     *
     * @return RoutePipeline A fully constructed pipeline configured with resolved middleware.
     */
    public function create(RouteDefinition $route) : RoutePipeline
    {
        // Resolving middleware definitions from the route into callable middleware instances.
        $resolvedMiddleware = $this->middlewareResolver->resolve($route->middleware);

        // Constructing a new RoutePipeline with the resolved dependencies and injecting middleware.
        // This step prepares the pipeline to handle HTTP requests for the given route.
        return (new RoutePipeline(
            route     : $route,       // Injecting the route definition into the pipeline.
            dispatcher: $this->dispatcher, // Injecting the dispatcher for controller execution.
            container : $this->container  // Injecting the IoC container for dependency resolution.
        ))->through(middleware: $resolvedMiddleware); // Configuring the pipeline with the resolved middleware.
    }
}