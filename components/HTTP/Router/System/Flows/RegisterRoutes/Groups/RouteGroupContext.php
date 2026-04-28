<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Groups;

use Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Definitions\RouteBuilder;

/**
 * Context object for route group configuration.
 */
final class RouteGroupContext
{
    public string       $prefix        = '';
    public string       $namePrefix    = '';
    private string|null $domain        = null;
    private string|null $authorization = null;
    private array       $middleware    = [];
    private array       $constraints   = [];
    private array       $defaults      = [];
    private array       $attributes    = [];

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
            $builder->whereIn($this->constraints);
        }
        if (! empty($this->defaults)) {
            $builder->defaults($this->defaults);
        }
        if (! empty($this->attributes)) {
            $builder->attributes($this->attributes);
        }

        return $builder;
    }

    public function setDomain(string $domain) : void { $this->domain = $domain; }

    public function setAuthorization(string $authorization) : void { $this->authorization = $authorization; }

    public function addMiddleware(array $middleware) : void
    {
        $this->middleware = array_merge($this->middleware, $middleware);
    }

    public function addConstraints(array $constraints) : void
    {
        $this->constraints = array_merge($this->constraints, $constraints);
    }

    public function addDefaults(array $defaults) : void
    {
        $this->defaults = array_merge($this->defaults, $defaults);
    }

    public function addAttributes(array $attributes) : void
    {
        $this->attributes = array_merge($this->attributes, $attributes);
    }

    public function merge(self $other) : self
    {
        $merged                = new self();
        $merged->prefix        = $this->prefix . $other->prefix;
        $merged->namePrefix    = $this->namePrefix . $other->namePrefix;
        $merged->domain        = $other->domain ?? $this->domain;
        $merged->authorization = $other->authorization ?? $this->authorization;
        $merged->middleware    = array_merge($this->middleware, $other->middleware);
        $merged->constraints   = array_merge($this->constraints, $other->constraints);
        $merged->defaults      = array_merge($this->defaults, $other->defaults);
        $merged->attributes    = array_merge($this->attributes, $other->attributes);

        return $merged;
    }
}
