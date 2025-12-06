<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing;

/**
 * Proxy that wraps a RouteBuilder and lazily registers the route
 * only once it's finalized (via name(), build(), or register()).
 *
 * Promotes clean chaining via fluent DSL.
 */
final class RouteRegistrarProxy
{
    private readonly RouteBuilder      $builder;

    private readonly HttpRequestRouter $router;

    private bool                       $registered = false;

    /**
     * Initializes proxy with Router and Builder.
     *
     * @param HttpRequestRouter $router
     * @param RouteBuilder      $builder
     */
    public function __construct(HttpRequestRouter $router, RouteBuilder $builder)
    {
        $this->router  = $router;
        $this->builder = $builder;
    }

    /**
     * Assigns a route name and triggers registration.
     *
     * @param string $name
     *
     * @return self
     */
    public function name(string $name) : self
    {
        $this->builder->name($name);

        return $this->register();
    }

    /**
     * Explicitly triggers registration (if not already).
     *
     * @return self
     */
    public function register() : self
    {
        if (! $this->registered) {
            $definition = $this->builder->build();

            $this->router->registerRoute(
                method       : $definition->method,
                path         : $definition->path,
                action       : $definition->action,
                middleware   : $definition->middleware,
                name         : $definition->name,
                constraints  : $definition->constraints,
                defaults     : $definition->defaults,
                domain       : $definition->domain,
                attributes   : $definition->attributes,
                authorization: $definition->authorization
            );

            $this->registered = true;
        }

        return $this;
    }

    /**
     * Finalizes and returns the RouteDefinition (registers first).
     *
     * @return RouteDefinition
     */
    public function build() : RouteDefinition
    {
        $this->register();

        return $this->builder->build();
    }

    /**
     * Adds a single route parameter constraint.
     */
    public function where(string $param, string $pattern) : self
    {
        $this->builder->where($param, $pattern);

        return $this;
    }

    /**
     * Adds multiple constraints.
     */
    public function whereIn(array $constraints) : self
    {
        $this->builder->whereIn($constraints);

        return $this;
    }

    /**
     * Defines default values for parameters.
     */
    public function defaults(array $defaults) : self
    {
        $this->builder->defaults($defaults);

        return $this;
    }

    /**
     * Defines custom route metadata.
     */
    public function attributes(array $attributes) : self
    {
        $this->builder->attributes($attributes);

        return $this;
    }

    /**
     * Attaches middleware to the route.
     */
    public function middleware(array $middleware) : self
    {
        $this->builder->middleware($middleware);

        return $this;
    }

    /**
     * Sets the authorization policy.
     */
    public function authorize(string $policy) : self
    {
        $this->builder->authorize($policy);

        return $this;
    }

    /**
     * Sets the controller + method for the route.
     */
    public function controller(string $controller, string $method = 'index') : self
    {
        $this->builder->controller($controller, $method);

        return $this;
    }

    /**
     * Sets the action callback or controller.
     */
    public function action(callable|array|string $action) : self
    {
        $this->builder->action($action);

        return $this;
    }
}
