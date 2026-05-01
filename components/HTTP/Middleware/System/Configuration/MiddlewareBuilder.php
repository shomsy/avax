<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Middleware\System\Configuration;

use Avax\Components\HTTP\Middleware\System\PublicSurface\MiddlewareInterface;

/**
 * Builder for constructing a middleware pipeline.
 *
 * Usage:
 *   $pipeline = (new MiddlewareBuilder())
 *       ->add($authMiddleware)
 *       ->add($loggingMiddleware)
 *       ->add($corsMiddleware)
 *       ->build();
 */
final class MiddlewareBuilder
{
    /** @var list<MiddlewareInterface> */
    private array $middleware = [];

    private ?MiddlewareInterface $fallback = null;

    /**
     * Add middleware to the pipeline (appended to the end).
     */
    public function add(MiddlewareInterface $middleware): self
    {
        $self = clone $this;
        $self->middleware[] = $middleware;

        return $self;
    }

    /**
     * Add middleware at the beginning of the pipeline (highest priority).
     */
    public function prepend(MiddlewareInterface $middleware): self
    {
        $self = clone $this;
        $self->middleware = [$middleware, ...$self->middleware];

        return $self;
    }

    /**
     * Add multiple middleware at once.
     *
     * @param list<MiddlewareInterface> $middleware
     */
    public function addMany(array $middleware): self
    {
        $self = clone $this;
        $self->middleware = [...$self->middleware, ...$middleware];

        return $self;
    }

    /**
     * Set a fallback handler for when no middleware matches.
     */
    public function withFallback(MiddlewareInterface $fallback): self
    {
        $self = clone $this;
        $self->fallback = $fallback;

        return $self;
    }

    /**
     * Build and return the ordered middleware list.
     *
     * @return list<MiddlewareInterface>
     */
    public function build(): array
    {
        return $this->middleware;
    }

    /**
     * Get the registered middleware count.
     */
    public function count(): int
    {
        return count($this->middleware);
    }

    /**
     * Get the fallback handler.
     */
    public function fallback(): ?MiddlewareInterface
    {
        return $this->fallback;
    }

    /**
     * Check if the pipeline is empty.
     */
    public function isEmpty(): bool
    {
        return $this->middleware === [];
    }
}
