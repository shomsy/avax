<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing;

/**
 * Represents a context for grouping multiple routes with shared properties.
 *
 * This class is utilized to apply shared configurations such as prefixes, middleware,
 * domain, and authorization to a group of routes in a router.
 *
 * @package Avax\HTTP\Router\Routing
 */
final class RouteGroupContext
{
    /**
     * Prefix for naming routes within this group.
     *
     * This property is prepended to all route names in the group,
     * providing a consistent and unique identifier structure.
     *
     * @var string|null
     */
    public string|null $namePrefix  = null;

    public array       $constraints = [];

    public array       $defaults    = [];

    public array       $attributes  = [];

    /**
     * Constructor for initializing the RouteGroupContext with optional settings.
     *
     * @param string|null $prefix        The path prefix to be applied to all routes in the group.
     * @param array|null  $middleware    A list of middleware classes to be applied to routes in the group.
     * @param string|null $domain        The domain constraint to be applied to all routes in the group.
     * @param string|null $authorization The authorization policy applied to all routes in the group.
     */
    public function __construct(
        public string|null $prefix = null,
        public array|null  $middleware = null,
        public string|null $domain = null,
        public string|null $authorization = null,
    ) {}

    /**
     * Sets a name prefix for the route group.
     *
     * Route names within this group will be prefixed with the provided value.
     * Trailing dots are ensured to maintain a proper naming convention.
     *
     * @param string $prefix The prefix to apply to the route names.
     *
     * @return void
     */
    public function setNamePrefix(string $prefix) : void
    {
        $this->namePrefix = rtrim($prefix, '.') . '.';
    }

    /**
     * Apply the defined customizations to the given `RouteBuilder`.
     *
     * The method modifies the `RouteBuilder` in place by:
     * - Prepending the configured `$prefix` to the route's path.
     * - Merging middleware, constraints, defaults, and attributes with the builder's existing properties.
     * - Assigning a custom domain and authorization, if specified.
     * - Applying a name prefix for route naming conventions.
     *
     * @param RouteBuilder $builder The route builder instance to be customized.
     *
     * @return RouteBuilder Returns the modified route builder instance.
     */
    public function applyTo(RouteBuilder $builder) : RouteBuilder
    {
        // If a prefix is defined, prepend it to the route's existing path.
        if ($this->prefix !== null) {
            $builder->path = rtrim($this->prefix, '/') . $builder->path;
        }

        // If middleware is defined, merge it with the existing middleware stack.
        if (! empty($this->middleware)) {
            $builder->middleware = array_merge($builder->middleware, $this->middleware);
        }

        // If a domain is set, replace the builder's existing domain with the configured one.
        if ($this->domain !== null) {
            $builder->domain = $this->domain;
        }

        // If an authorization configuration exists, assign it to the builder.
        if ($this->authorization !== null) {
            $builder->authorization = $this->authorization;
        }

        // Merge the defined constraints with the builder's existing constraints.
        if (! empty($this->constraints)) {
            $builder->constraints = array_merge($builder->constraints, $this->constraints);
        }

        // Merge the defined defaults with the builder's existing default values.
        if (! empty($this->defaults)) {
            $builder->defaults = array_merge($builder->defaults, $this->defaults);
        }

        // Merge the defined attributes with the builder's existing attributes.
        if (! empty($this->attributes)) {
            $builder->attributes = array_merge($builder->attributes, $this->attributes);
        }

        // If a name prefix is defined, apply it to the route's name, maintaining naming conventions.
        if ($this->namePrefix !== null && $builder->name !== null) {
            $builder->name = $this->namePrefix . $builder->name;
        }

        // Return the modified RouteBuilder instance.
        return $builder;
    }


    /**
     * Sets the path prefix for the current route group.
     *
     * This path prefix provides a structured namespace for all routes
     * within the group, ensuring a logical URL hierarchy.
     *
     * @param string $prefix The route path prefix, typically a non-empty string.
     *                       For example: "api/v1" or "admin".
     *
     * @return void
     */
    public function setPrefix(string $prefix) : void
    {
        // Normalize the prefix by trimming trailing slashes to prevent
        // issues with inconsistent path generation.
        $this->prefix = rtrim($prefix, '/');
    }

    /**
     * Adds additional middleware to the group context.
     *
     * This method allows appending new middleware handlers onto the
     * existing middleware stack to provide a flexible, composable
     * routing pipeline.
     *
     * @param array $middleware A list of middleware to add, each represented
     *                          typically as a callable or handler class name.
     *
     * @return void
     */
    public function addMiddleware(array $middleware) : void
    {
        // Initialize middleware stack if not set.
        $this->middleware ??= [];

        // Merge the new middleware with the existing stack, ensuring the existing
        // middleware and new handlers are preserved.
        $this->middleware = array_merge($this->middleware, $middleware);
    }

    /**
     * Sets the domain constraint for all routes in the group.
     *
     * This domain constraint ensures that the routes in the group
     * are only accessible from a specific domain.
     *
     * @param string $domain The domain to apply to the route group.
     *
     * @return void
     */
    public function setDomain(string $domain) : void
    {
        $this->domain = $domain;
    }

    /**
     * Sets the authorization policy for all routes in the group.
     *
     * This policy defines the access control handling for the routes
     * making it a critical part of securing route groups.
     *
     * @param string $authorization The authorization policy identifier or configuration.
     *
     * @return void
     */
    public function setAuthorization(string $authorization) : void
    {
        $this->authorization = $authorization;
    }

    /**
     * Adds default values to this route group context.
     *
     * Defaults are used to fill in missing values for route parameters
     * when they are not explicitly provided in the request.
     *
     * @param array<string, mixed> $defaults Key-value pairs of default values to add.
     *
     * @return void
     */
    public function addDefaults(array $defaults) : void
    {
        // Merge the new defaults into the existing defaults
        $this->defaults = array_merge($this->defaults, $defaults);
    }

    /**
     * Adds attributes to the route group context.
     *
     * Attributes are useful for providing metadata or additional
     * information for routing, middleware, or custom processing logic.
     *
     * @param array<string, mixed> $attributes Key-value pairs of attributes to add.
     *
     * @return void
     */
    public function addAttributes(array $attributes) : void
    {
        // Merge the new attributes into the existing attributes
        $this->attributes = array_merge($this->attributes, $attributes);
    }

    /**
     * Adds parameter constraints (regex patterns) to the route group context.
     *
     * These constraints will be merged and applied to all routes within the group.
     *
     * @param array<string, string> $constraints Parameter name to regex mapping.
     *
     * @return void
     */
    public function addConstraints(array $constraints) : void
    {
        $this->constraints = array_merge($this->constraints, $constraints);
    }

}