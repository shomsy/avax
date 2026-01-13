<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Support;

use Avax\HTTP\Router\Routing\RouteBuilder;
use Closure;

/**
 * ROUTE LIFECYCLE: DSL → Collection → Registration
 *
 * RouteCollector is the critical bridge between route DSL execution and router registration.
 * Refactored to be instance-based to eliminate global state and enable proper isolation.
 *
 * ## Route Lifecycle Flow:
 * 1. **DSL Execution** (RouteRegistrar::load)
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
 * 2. **Registration** (RouteBootstrapper)
 *    - Fallback passed to HttpRequestRouter::fallback()
 *    - Stored in HttpRequestRouter for runtime resolution
 *
 * 3. **Runtime Invocation** (Router::resolve)
 *    - Called when no routes match the request
 *    - Handled by FallbackManager for controller-style dispatch
 *
 * ## Thread Safety Benefits:
 * - No static properties = no race conditions between threads
 * - Instance isolation = no cross-context contamination
 * - Predictable state management = deterministic behavior
 */
final class RouteCollector
{
    /**
     * @var RouteBuilder[] Buffered route builders
     */
    private array $routes = [];

    /**
     * @var callable|null Fallback route handler
     */
    private $fallback = null;

    /**
     * Execute a closure with scoped route collection.
     *
     * Creates a new RouteCollector instance for the closure execution,
     * ensuring complete isolation between different route loading contexts.
     * Eliminates global state by passing collector as closure parameter.
     *
     * @param Closure $closure The closure to execute with scoped collection
     * @return RouteCollector The collector instance used for the scope
     */
    public static function scoped(Closure $closure) : self
    {
        $collector = new self();

        // Execute closure with collector instance passed as parameter
        // This eliminates global state completely
        try {
            $closure($collector);
            return $collector;
        } catch (\Throwable $exception) {
            // Ensure clean state even on exception
            $collector->clear();
            throw $exception;
        }
    }

    /**
     * Execute DSL functions with this collector instance.
     *
     * Provides a clean API for route file execution without global state.
     *
     * @param string $code The PHP code containing DSL function calls
     * @return void
     */
    public function executeDsl(string $code) : void
    {
        // Bind this collector instance to the execution context
        $collector = $this;

        // Create isolated execution environment
        $executionClosure = function () use ($code, $collector) : void {
            // Make collector available to global DSL functions via closure binding
            // This replaces the global state approach
            $dslFunctions = [
                'get' => fn($path, $action) => $collector->addRouteBuilder(
                    \Avax\HTTP\Router\Routing\RouteBuilder::make('GET', $path)->action($action)
                ),
                'post' => fn($path, $action) => $collector->addRouteBuilder(
                    \Avax\HTTP\Router\Routing\RouteBuilder::make('POST', $path)->action($action)
                ),
                'put' => fn($path, $action) => $collector->addRouteBuilder(
                    \Avax\HTTP\Router\Routing\RouteBuilder::make('PUT', $path)->action($action)
                ),
                'patch' => fn($path, $action) => $collector->addRouteBuilder(
                    \Avax\HTTP\Router\Routing\RouteBuilder::make('PATCH', $path)->action($action)
                ),
                'delete' => fn($path, $action) => $collector->addRouteBuilder(
                    \Avax\HTTP\Router\Routing\RouteBuilder::make('DELETE', $path)->action($action)
                ),
                'options' => fn($path, $action) => $collector->addRouteBuilder(
                    \Avax\HTTP\Router\Routing\RouteBuilder::make('OPTIONS', $path)->action($action)
                ),
                'head' => fn($path, $action) => $collector->addRouteBuilder(
                    \Avax\HTTP\Router\Routing\RouteBuilder::make('HEAD', $path)->action($action)
                ),
                'any' => fn($path, $action) => $collector->addRouteBuilder(
                    \Avax\HTTP\Router\Routing\RouteBuilder::make('ANY', $path)->action($action)
                ),
                'fallback' => fn($handler) => $collector->setFallback($handler),
            ];

            // Extract variables for the DSL execution
            extract($dslFunctions);

            // Execute the DSL code in isolated scope
            eval('?>' . $code);
        };

        $executionClosure();
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
     * Add a route builder and return a registrar proxy for chaining.
     *
     * This method provides the fluent API for DSL functions.
     *
     * @param RouteBuilder $routeBuilder The route builder to add
     * @return \Avax\HTTP\Router\Routing\RouteRegistrarProxy The proxy for chaining
     */
    public function addRouteBuilder(RouteBuilder $routeBuilder) : \Avax\HTTP\Router\Routing\RouteRegistrarProxy
    {
        $this->add($routeBuilder);

        return new \Avax\HTTP\Router\Routing\RouteRegistrarProxy(
            router: null, // Will be set by RouterDsl
            builder: $routeBuilder,
            registry: null // Will be set by RouterDsl
        );
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
        return $this->fallback;
    }

    /**
     * Set the fallback route handler.
     *
     * @param callable $fallback The fallback handler
     */
    public function setFallback(callable $fallback) : void
    {
        $this->fallback = $fallback;
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
        return count($this->routes);
    }

    /**
     * Clear all routes (for testing/cleanup).
     */
    public function clear() : void
    {
        $this->routes = [];
        $this->fallback = null;
    }
}