<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing;

use Avax\Auth\Interface\HTTP\Middleware\AuthorizeMiddleware;
use Avax\Container\Features\Core\Contracts\ContainerInterface;
use Avax\HTTP\Dispatcher\ControllerDispatcher;
use Avax\HTTP\Request\Request;
use Laravel\SerializableClosure\SerializableClosure;
use Psr\Http\Message\ResponseInterface;

/**
 * Class RoutePipeline
 *
 * Manages the execution pipeline for handling incoming HTTP requests
 * through applied middleware, optional route stages, and finally dispatching
 * the matched route action to the appropriate controller.
 *
 * Responsibilities:
 * - Applies middleware and route-specific stages (e.g., logging, tracing).
 * - Injects route-level authorization middleware when necessary.
 * - Provides a fluent API for pipeline configuration.
 */
final class RoutePipeline
{
    /**
     * List of middleware to be applied in the processing pipeline.
     *
     * @var array<string|class-string>
     */
    private array $middleware = [];

    /**
     * Optional predefined stages (e.g., for logging, tracing) to augment the pipeline.
     *
     * @var array<class-string<RouteStage>>
     */
    private array $stages = [];

    /**
     * Constructor
     *
     * Initializes the pipeline using the provided route definition and dispatcher.
     *
     * @param RouteDefinition      $route      The route definition to bind to the pipeline.
     * @param ControllerDispatcher $dispatcher Handles the final dispatching of the controller action.
     */
    public function __construct(
        private readonly RouteDefinition      $route,
        private readonly ControllerDispatcher $dispatcher,
        private readonly ContainerInterface   $container,
        private readonly StageChain           $stageChain
    ) {}

    /**
     * Factory method for constructing the pipeline instance
     * with the route and dispatcher, promoting fluent API usage.
     *
     * @param RouteDefinition      $route      The route definition to be handled.
     * @param ControllerDispatcher $dispatcher Used to invoke controller methods.
     *
     * @return self A new instance of RoutePipeline.
     */
    public static function for(
        RouteDefinition      $route,
        ControllerDispatcher $dispatcher,
        ContainerInterface   $container
    ) : self
    {
        return new self(
            route     : $route,
            dispatcher: $dispatcher,
            container : $container
        );
    }

    /**
     * Adds middleware to the processing pipeline.
     *
     * Allows dynamic insertion of middleware for the current route processing.
     *
     * @param array<string|class-string> $middleware Array of middleware class names or middleware identifiers.
     *
     * @return self The current instance, for fluent API usage.
     */
    public function through(array $middleware) : self
    {
        $this->middleware = $middleware;

        return $this;
    }

    /**
     * Adds optional stages to the processing pipeline.
     *
     * Stages add auxiliary functionality to the route processing, like logging
     * or telemetry tracking, without interfering with core middleware logic.
     *
     * @param array<class-string<RouteStage>> $stages List of stage class names.
     *
     * @return self The current instance, for fluent API chaining.
     */
    public function stages(array $stages) : self
    {
        $this->stages = $stages;

        return $this;
    }

    /**
     * Dispatches a request through the pipeline.
     *
     * The dispatch process follows these steps:
     * - Optionally injects authorization middleware if the route requires it.
     * - Builds the middleware pipeline, including optional stages.
     * - Executes the pipeline, ultimately invoking the associated route action.
     *
     * @param Request $request The current HTTP request to process.
     *
     * @return ResponseInterface The final HTTP response from the dispatched route.
     *
     * @throws \ReflectionException If reflection fails during middleware creation.
     * @throws \Psr\Container\ContainerExceptionInterface If the DI container encounters an issue.
     * @throws \Psr\Container\NotFoundExceptionInterface If a middleware class cannot be resolved.
     */
    public function dispatch(Request $request) : ResponseInterface
    {
        try {
            // Inject route authorization into the request if a policy is defined.
            if ($this->route->authorization !== null) {
                // Attach the authorization policy as a request attribute.
                $request = $request->withAttribute(name: 'route:authorization', value: $this->route->authorization);

                // Prepend the authorization middleware to the pipeline.
                array_unshift($this->middleware, AuthorizeMiddleware::class);
            }

            // Unserialize the route action if it's a SerializableClosure
            $route = $this->route;
            if ($route->action instanceof SerializableClosure) {
                $route = $route->withUnserializedAction();
            }

            // Define the core execution logic for the pipeline - dispatching the route's action.
            $core = fn(Request $request) : ResponseInterface => $this->dispatcher->dispatch(
                action : $route->action,
                request: $request
            );

            // Build the ordered pipeline using StageChain diagnostics.
            $stack = $this->stageChain->create(
                stages    : $this->stages,
                middleware: $this->middleware,
                core      : $core
            );

            return $stack($request);
        } catch (\Throwable $e) {
            // Return 500 Response on exceptions
            return new \Avax\HTTP\Response\Classes\Response(
                stream: \Avax\HTTP\Response\Classes\Stream::fromString('Internal Server Error'),
                statusCode: 500
            );
        }
    }
}