<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing;

use Avax\Container\Features\Core\Contracts\ContainerInterface;
use Avax\HTTP\Dispatcher\ControllerDispatcher;
use Avax\HTTP\Middleware\MiddlewareResolver;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

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
        private StageChain           $stageChain,
        private LoggerInterface      $logger = new NullLogger,
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
        $resolvedMiddleware = $this->middlewareResolver->resolve(middleware: $route->middleware);

        $this->logger->debug(message: 'Assembling route pipeline.', context: [
            'route'      => $route->name ?: $route->path,
            'middleware' => $resolvedMiddleware,
            'stages'     => [],
            'order'      => ['stages', 'middleware', 'dispatch'],
        ]);

        // Constructing a new RoutePipeline with the resolved dependencies and injecting middleware.
        // This step prepares the pipeline to handle HTTP requests for the given route.
        return (new RoutePipeline(
            route     : $route,
            dispatcher: $this->dispatcher,
            container : $this->container,
            stageChain: $this->stageChain
        ))->through(middleware: $resolvedMiddleware);
    }
}
