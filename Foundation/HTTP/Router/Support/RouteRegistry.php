<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Support;

use Avax\HTTP\Router\Routing\RouteBuilder;

/**
 * RouteRegistry stores routes defined during bootstrap and cache compilation.
 *
 * This class replaces the former global RouteCollector. Each bootstrap/compiler
 * cycle should share a registry instance to avoid leaking buffered routes.
 */
final class RouteRegistry
{
    /**
     * @var list<RouteBuilder>
     */
    private array $bufferedRoutes = [];

    private mixed $fallback = null;

    /**
     * Adds a builder to the buffered list.
     */
    public function add(RouteBuilder $builder) : void
    {
        $this->bufferedRoutes[] = $builder;
    }

    /**
     * Flushes the buffered builders and resets the buffer.
     *
     * @return list<RouteBuilder>
     */
    public function flush() : array
    {
        $routes               = $this->bufferedRoutes;
        $this->bufferedRoutes = [];

        return $routes;
    }

    /**
     * Gets the current fallback handler.
     */
    public function getFallback() : mixed
    {
        return $this->fallback;
    }

    /**
     * Sets the fallback handler.
     */
    public function setFallback(mixed $handler) : void
    {
        $this->fallback = $handler;
    }

    /**
     * Checks if a fallback handler exists.
     */
    public function hasFallback() : bool
    {
        return $this->fallback !== null;
    }

    /**
     * Executes callback with isolated state and restores afterwards.
     *
     * @template T
     *
     * @param callable():T $callback
     *
     * @return T
     */
    public function scoped(callable $callback)
    {
        $previousRoutes   = $this->bufferedRoutes;
        $previousFallback = $this->fallback;

        $this->reset();

        try {
            return $callback();
        } finally {
            $this->bufferedRoutes = $previousRoutes;
            $this->fallback       = $previousFallback;
        }
    }

    /**
     * Resets the registry state.
     */
    public function reset() : void
    {
        $this->bufferedRoutes = [];
        $this->fallback       = null;
    }
}
