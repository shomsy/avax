<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing;

use Avax\HTTP\Router\Support\RouteRegistry;

/**
 * Proxy that wraps a RouteBuilder and lazily registers the route
 * only once it's finalized (via name(), build(), or register()).
 *
 * Promotes clean chaining via fluent DSL.
 */
final class RouteRegistrarProxy
{
    private readonly RouteBuilder $builder;

    private readonly HttpRequestRouter $router;

    private readonly RouteRegistry $registry;

    private bool $registered = false;

    /**
     * Initializes proxy with Router and Builder.
     */
    public function __construct(HttpRequestRouter $router, RouteBuilder $builder, RouteRegistry $registry)
    {
        $this->router   = $router;
        $this->builder  = $builder;
        $this->registry = $registry;
    }

    /**
     * Assigns a route name and triggers registration.
     */
    public function name(string $name) : self
    {
        $this->builder->name(name: $name);

        $this->register();

        return $this;
    }

    /**
     * Explicitly triggers registration (if not already).
     */
    public function register() : self
    {
        if (! $this->registered) {
            // Add route builder to registry for later processing
            $this->registry->add(builder: $this->builder);

            $this->registered = true;
        }

        return $this;
    }

    /**
     * Finalizes and returns the RouteDefinition (registers first).
     *
     * @internal Use fluent methods instead.
     */
    public function build() : RouteDefinition
    {
        return $this->builder->build();
    }

    /**
     * Adds a single route parameter constraint.
     */
    public function where(string $param, string $pattern) : self
    {
        $this->builder->where(parameter: $param, pattern: $pattern);

        return $this;
    }

    /**
     * Adds multiple constraints.
     */
    public function whereIn(array $constraints) : self
    {
        $this->builder->whereIn(constraints: $constraints);

        return $this;
    }

    /**
     * Defines default values for parameters.
     */
    public function defaults(array $defaults) : self
    {
        $this->builder->defaults(defaults: $defaults);

        return $this;
    }

    /**
     * Defines custom route metadata.
     */
    public function attributes(array $attributes) : self
    {
        $this->builder->attributes(attributes: $attributes);

        return $this;
    }

    /**
     * Attaches middleware to the route.
     */
    public function middleware(array $middleware) : self
    {
        $this->builder->middleware(middleware: $middleware);

        return $this;
    }

    /**
     * Sets the authorization policy.
     */
    public function authorize(string $policy) : self
    {
        $this->builder->authorize(policy: $policy);

        return $this;
    }

    /**
     * Sets the controller + method for the route.
     */
    public function controller(string $controller, string $method = 'index') : self
    {
        $this->builder->controller(controller: $controller, method: $method);

        return $this;
    }

    /**
     * Sets the action callback or controller.
     */
    public function action(callable|array|string $action) : self
    {
        $this->builder->action(action: $action);

        return $this;
    }

    /**
     * Auto-register route when proxy is destroyed (end of statement).
     */
    public function __destruct()
    {
        $this->register();
    }
}