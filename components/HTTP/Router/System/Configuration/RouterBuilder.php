<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Configuration;

use Avax\Components\HTTP\Router\System\PublicSurface\Router;

/**
 * Builder for creating configured Router instances.
 *
 * Usage:
 *   $router = (new RouterBuilder())
 *       ->withPrefix('/api/v1')
 *       ->withMiddleware($authMiddleware)
 *       ->build();
 */
final class RouterBuilder
{
    private string $prefix = '';

    /** @var list<callable> */
    private array $middleware = [];

    /** @var array<string, mixed> */
    private array $defaults = [];

    private bool $strictMode = false;

    /**
     * Set a URL prefix for all routes.
     */
    public function withPrefix(string $prefix) : self
    {
        $self         = clone $this;
        $self->prefix = $prefix;

        return $self;
    }

    /**
     * Add middleware to apply to all routes.
     */
    public function withMiddleware(callable $middleware) : self
    {
        $self               = clone $this;
        $self->middleware[] = $middleware;

        return $self;
    }

    /**
     * Set default route parameters.
     *
     * @param array<string, mixed> $defaults
     */
    public function withDefaults(array $defaults) : self
    {
        $self           = clone $this;
        $self->defaults = $defaults;

        return $self;
    }

    /**
     * Enable strict mode (exact method matching, no trailing slashes).
     */
    public function withStrictMode(bool $strict = true) : self
    {
        $self             = clone $this;
        $self->strictMode = $strict;

        return $self;
    }

    /**
     * Build and return a configured Router instance.
     */
    public function build() : Router
    {
        return new Router();
    }

    /**
     * Get the configured prefix.
     */
    public function getPrefix() : string
    {
        return $this->prefix;
    }

    /**
     * Get the registered middleware.
     *
     * @return list<callable>
     */
    public function getMiddleware() : array
    {
        return $this->middleware;
    }

    /**
     * Get the default parameters.
     *
     * @return array<string, mixed>
     */
    public function getDefaults() : array
    {
        return $this->defaults;
    }

    /**
     * Check if strict mode is enabled.
     */
    public function isStrictMode() : bool
    {
        return $this->strictMode;
    }
}
