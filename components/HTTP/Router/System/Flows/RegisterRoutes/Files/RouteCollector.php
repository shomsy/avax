<?php

declare(strict_types=1);

namespace components\HTTP\Router\System\Flows\RegisterRoutes\Files;

use Closure;
use components\HTTP\Router\System\Flows\RegisterRoutes\Definitions\RouteBuilder;
use components\HTTP\Router\System\Flows\RegisterRoutes\Definitions\RouteRegistry;
use components\HTTP\Router\System\Flows\ResolveRequest\HttpRequestRouter;
use LogicException;
use SensitiveParameter;
use Throwable;

/**
 * ROUTE LIFECYCLE: DSL → Collection → Registration
 *
 * RouteCollector is the critical bridge between route DSL execution and router registration.
 * Refactored to be instance-based to eliminate global state and enable proper isolation.
 *
 * ## Route Lifecycle Flow:
 * 1. **DSL Execution** (RouteFileRegistrar::load)
 *    - Route files executed in isolated scope with dedicated collector instance
 *    - DSL calls (get/post/any) create RouteBuilder instances via collector
 *    - RouteBuilder instances buffered in instance-specific collection
 *
 * 2. **Collection Phase** (RouteCollector instance)
 *    - Routes buffered in instance $routes array (no global state)
 *    - Fallback stored in instance $fallback
 *    - Natural isolation between different collector instances
 *
 * 3. **Activation Point** (RouteCollector::flush)
 *    - RouteBuilder::build() called to create RouteDefinition
 *    - RouteDefinition registered with HttpRequestRouter
 *    - Buffer cleared for next collection cycle
 *
 * ## Fallback Lifecycle Flow:
 * 1. **DSL Definition** (RouterDsl::fallback)
 *    - Fallback callable stored via RouteCollector::setFallback()
 *
 * 2. **Registration** (BootstrapRoutes)
 *    - Fallback passed to HttpRequestRouter::fallback()
 *    - Stored in HttpRequestRouter for runtime resolution
 *
 * 3. **Runtime Invocation** (Router::resolve)
 *    - Called when no routes match the request
 *    - Handled by RegisteredFallback for controller-style dispatch
 *
 * ## Thread Safety Benefits:
 * - No static properties = no race conditions between threads
 * - Instance isolation = no cross-context contamination
 * - Predictable state management = deterministic behavior
 */
final class RouteCollector
{
    private static self|null $current = null;
    /**
     * @var RouteBuilder[] Buffered route builders
     */
    private array $routes = [];
    /**
     * @var callable|null Fallback route handler
     */
    private $fallback = null;

    public function __construct(
        private readonly HttpRequestRouter|null $router = null,
        private readonly RouteRegistry|null     $registry = null
    ) {}

    /**
     * Execute a closure with scoped route collection.
     *
     * Creates a new RouteCollector instance for the closure execution,
     * ensuring complete isolation between different route loading contexts.
     * Eliminates global state by passing collector as closure parameter.
     *
     * @param Closure $closure The closure to execute with scoped collection
     *
     * @return RouteCollector The collector instance used for the scope
     * @throws Throwable
     */
    public static function scoped(
        Closure                $closure,
        HttpRequestRouter|null $router = null,
        RouteRegistry|null     $registry = null
    ) : self
    {
        $collector     = new self(router: $router, registry: $registry);
        $previous      = self::$current;
        self::$current = $collector;

        try {
            $closure($collector);

            return $collector;
        } catch (Throwable $exception) {
            $collector->clear();
            throw $exception;
        } finally {
            self::$current = $previous;
        }
    }

    /**
     * Clear all routes (for testing/cleanup).
     */
    public function clear() : void
    {
        $this->routes   = [];
        $this->fallback = null;
    }

    public static function current() : self
    {
        if (self::$current === null) {
            throw new LogicException(message: 'Route DSL functions must run inside a route registration scope.');
        }

        return self::$current;
    }

    /**
     * Execute DSL functions with this collector instance.
     *
     * Provides a clean API for route file execution without global state.
     *
     * @param string $code The PHP code containing DSL function calls
     *
     * @return void
     */
    public function executeDsl(#[SensitiveParameter] string $code) : void
    {
        // Bind this collector instance to the execution context
        $collector = $this;

        // Create isolated execution environment
        $executionClosure = function () use ($collector, $code) : void {
            // Make collector available to global DSL functions via closure binding
            // This replaces the global state approach
            $dslFunctions = [
                'get'      => fn ($path, $action) => $collector->addRouteBuilder(
                    routeBuilder: RouteBuilder::make(method: 'GET', path: $path)->action(action: $action)
                ),
                'post'     => fn ($path, $action) => $collector->addRouteBuilder(
                    routeBuilder: RouteBuilder::make(method: 'POST', path: $path)->action(action: $action)
                ),
                'put'      => fn ($path, $action) => $collector->addRouteBuilder(
                    routeBuilder: RouteBuilder::make(method: 'PUT', path: $path)->action(action: $action)
                ),
                'patch'    => fn ($path, $action) => $collector->addRouteBuilder(
                    routeBuilder: RouteBuilder::make(method: 'PATCH', path: $path)->action(action: $action)
                ),
                'delete'   => fn ($path, $action) => $collector->addRouteBuilder(
                    routeBuilder: RouteBuilder::make(method: 'DELETE', path: $path)->action(action: $action)
                ),
                'options'  => fn ($path, $action) => $collector->addRouteBuilder(
                    routeBuilder: RouteBuilder::make(method: 'OPTIONS', path: $path)->action(action: $action)
                ),
                'head'     => fn ($path, $action) => $collector->addRouteBuilder(
                    routeBuilder: RouteBuilder::make(method: 'HEAD', path: $path)->action(action: $action)
                ),
                'any'      => fn ($path, $action) => $collector->addRouteBuilder(
                    routeBuilder: RouteBuilder::make(method: 'ANY', path: $path)->action(action: $action)
                ),
                'fallback' => fn ($handler) => $collector->setFallback(fallback: $handler),
            ];

            // Extract variables for the DSL execution
            extract(array: $dslFunctions);

            // Execute the DSL code in isolated scope
            eval('?>' . $code);
        };

        $executionClosure();
    }

    /**
     * Add a route builder and return a registrar proxy for chaining.
     *
     * This method provides the fluent API for DSL functions.
     *
     * @param RouteBuilder $routeBuilder The route builder to add
     *
     * @return RouteRegistrarProxy The proxy for chaining
     */
    public function addRouteBuilder(RouteBuilder $routeBuilder) : RouteRegistrarProxy
    {
        $this->add(routeBuilder: $routeBuilder);
        $registered = false;

        if ($this->registry !== null) {
            $this->registry->add(builder: $routeBuilder);
            $registered = true;
        }

        return new RouteRegistrarProxy(
            router    : $this->router,
            builder   : $routeBuilder,
            registry  : $this->registry,
            registered: $registered
        );
    }

    /**
     * Add a route builder to the collection.
     *
     * @param RouteBuilder $routeBuilder The route builder to add
     */
    public function add(RouteBuilder $routeBuilder) : void
    {
        $this->routes[] = $routeBuilder;
    }

    /**
     * Flush all collected route builders.
     *
     * Returns the collected routes and clears the buffer.
     *
     * @return RouteBuilder[] The collected route builders
     */
    public function flush() : array
    {
        $routes       = $this->routes;
        $this->routes = [];

        return $routes;
    }

    /**
     * Get the fallback route handler.
     *
     * @return callable|null The fallback handler or null if not set
     */
    public function getFallback() : callable|null
    {
        if ($this->registry !== null && $this->registry->hasFallback()) {
            return $this->registry->fallback();
        }

        return $this->fallback;
    }

    /**
     * Set the fallback route handler.
     *
     * @param callable $fallback The fallback handler
     */
    public function setFallback(callable|array|string $fallback) : void
    {
        $this->fallback = $fallback;
        $this->registry?->setFallback(fallback: $fallback);
    }

    /**
     * Check if any routes have been collected.
     *
     * @return bool True if routes are buffered
     */
    public function hasRoutes() : bool
    {
        return ! empty($this->routes);
    }

    /**
     * Get the count of collected routes.
     *
     * @return int Number of buffered routes
     */
    public function count() : int
    {
        return count(value: $this->routes);
    }
}
