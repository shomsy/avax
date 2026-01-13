<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing;

/**
 * Context object for route group configuration.
 *
 * Encapsulates all group-level settings (prefix, middleware, constraints, etc.)
 * that apply to routes defined within the group scope.
 */
final class RouteGroupContext
{
    private string $prefix = '';
    private string $namePrefix = '';
    private string|null $domain = null;
    private string|null $authorization = null;

    /**
     * @var array<string>
     */
    private array $middleware = [];

    /**
     * @var array<string, string>
     */
    private array $constraints = [];

    /**
     * @var array<string, mixed>
     */
    private array $defaults = [];

    /**
     * @var array<string, mixed>
     */
    private array $attributes = [];

    /**
     * Apply this context to a route builder.
     */
    public function applyTo(RouteBuilder $builder) : RouteBuilder
    {
        if (! empty($this->prefix)) {
            $builder->prefix($this->prefix);
        }

        if (! empty($this->namePrefix)) {
            $builder->name($this->namePrefix);
        }

        if ($this->domain !== null) {
            $builder->domain($this->domain);
        }

        if ($this->authorization !== null) {
            $builder->authorize($this->authorization);
        }

        if (! empty($this->middleware)) {
            $builder->middleware($this->middleware);
        }

        if (! empty($this->constraints)) {
            $builder->where($this->constraints);
        }

        if (! empty($this->defaults)) {
            $builder->defaults($this->defaults);
        }

        if (! empty($this->attributes)) {
            $builder->attributes($this->attributes);
        }

        return $builder;
    }

    public function setPrefix(string $prefix) : void
    {
        $this->prefix = $prefix;
    }

    public function setNamePrefix(string $prefix) : void
    {
        $this->namePrefix = $prefix;
    }

    public function setDomain(string $domain) : void
    {
        $this->domain = $domain;
    }

    public function setAuthorization(string $authorization) : void
    {
        $this->authorization = $authorization;
    }

    /**
     * @param array<string> $middleware
     */
    public function addMiddleware(array $middleware) : void
    {
        $this->middleware = array_merge($this->middleware, $middleware);
    }

    /**
     * @param array<string, string> $constraints
     */
    public function addConstraints(array $constraints) : void
    {
        $this->constraints = array_merge($this->constraints, $constraints);
    }

    /**
     * @param array<string, mixed> $defaults
     */
    public function addDefaults(array $defaults) : void
    {
        $this->defaults = array_merge($this->defaults, $defaults);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function addAttributes(array $attributes) : void
    {
        $this->attributes = array_merge($this->attributes, $attributes);
    }

    /**
     * Create a new context by merging this context with another.
     */
    public function merge(self $other) : self
    {
        $merged = new self();

        $merged->prefix = $this->prefix . $other->prefix;
        $merged->namePrefix = $this->namePrefix . $other->namePrefix;
        $merged->domain = $other->domain ?? $this->domain;
        $merged->authorization = $other->authorization ?? $this->authorization;
        $merged->middleware = array_merge($this->middleware, $other->middleware);
        $merged->constraints = array_merge($this->constraints, $other->constraints);
        $merged->defaults = array_merge($this->defaults, $other->defaults);
        $merged->attributes = array_merge($this->attributes, $other->attributes);

        return $merged;
    }

    /**
     * Check if this context has any configuration.
     */
    public function isEmpty() : bool
    {
        return empty($this->prefix) &&
               empty($this->namePrefix) &&
               $this->domain === null &&
               $this->authorization === null &&
               empty($this->middleware) &&
               empty($this->constraints) &&
               empty($this->defaults) &&
               empty($this->attributes);
    }
}