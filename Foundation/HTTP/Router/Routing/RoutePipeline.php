<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing;

use Closure;
use Avax\Auth\Interface\HTTP\Middleware\AuthorizeMiddleware;
use Avax\Container\Contracts\ContainerInterface;
use Avax\HTTP\Dispatcher\ControllerDispatcher;
use Avax\HTTP\Request\Request;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

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
        private readonly ContainerInterface   $container
    ) {}

    /**
     * Factory method for constructing the pipeline instance
     * with the route and dispatcher, promoting fluent API usage.
     *
     * @param RouteDefinition      $route      The route definition to be handled.
     * @param ControllerDispatcher $dispatcher Used to invoke controller methods.
     *
     * @return self                        A new instance of RoutePipeline.
     */
    public static function for(
        RouteDefinition      $route,
        ControllerDispatcher $dispatcher,
        ContainerInterface   $container
    ) : self {
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
     * @throws \ReflectionException                     If reflection fails during middleware creation.
     * @throws \Psr\Container\ContainerExceptionInterface If the DI container encounters an issue.
     * @throws \Psr\Container\NotFoundExceptionInterface  If a middleware class cannot be resolved.
     */
    public function dispatch(Request $request) : ResponseInterface
    {
        // Inject route authorization into the request if a policy is defined.
        if ($this->route->authorization !== null) {
            // Attach the authorization policy as a request attribute.
            $request = $request->withAttribute(name: 'route:authorization', value: $this->route->authorization);

            // Prepend the authorization middleware to the pipeline.
            array_unshift($this->middleware, AuthorizeMiddleware::class);
        }

        // Define the core execution logic for the pipeline - dispatching the route's action.
        $core = fn(Request $request) : ResponseInterface => $this->dispatcher->dispatch(
            action : $this->route->action,
            request: $request
        );

        // Combine stages and middleware into a unified processing pipeline.
        $pipeline = array_merge($this->stages, $this->middleware);

        // Reduce the middleware and stages into a single processing stack (chain of responsibility).
        $stack = array_reduce(
        // Reverse the pipeline to ensure middleware are applied in the correct order.
            array_reverse($pipeline),
            // Accumulate middleware execution into the next stack function.
            fn(Closure $next, string $class) => function (Request $request) use (
                $class,
                $next
            ) : ResponseInterface {
                // Resolve the middleware or stage instance from the container.
                $instance = $this->container->get($class);

                // Ensure the middleware or stage has a `handle()` method.
                if (! method_exists($instance, 'handle')) {
                    throw new RuntimeException(
                        message: "Middleware or stage [{$class}] must have a handle() method."
                    );
                }

                // Call the middleware or stage's handle method, passing the request and next closure.
                return $instance->handle($request, $next);
            },
            // Start from the core action dispatcher.
            $core
        );

        // Execute the complete middleware stack with the initial request.
        return $stack($request);
    }
}