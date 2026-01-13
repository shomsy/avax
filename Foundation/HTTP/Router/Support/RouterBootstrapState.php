<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Support;

use RuntimeException;

/**
 * Thread-safe bootstrap state management for RouteBootstrapper.
 *
 * Replaces static state with instance-based state to ensure thread-safety
 * and proper isolation between different container instances.
 */
final class RouterBootstrapState
{
    private bool        $booted = false;
    private string|null $source = null;

    /**
     * Ensure the bootstrapper has not already been booted.
     *
     * @throws \RuntimeException If already bootstrapped
     */
    public function ensureNotBooted() : void
    {
        if ($this->booted) {
            throw new RuntimeException(message: 'Router bootstrapper has already been executed. Cannot bootstrap multiple times.');
        }
        $this->booted = true;
    }

    /**
     * Check if the bootstrapper has been booted.
     */
    public function isBooted() : bool
    {
        return $this->booted;
    }

    /**
     * Mark the source of route loading for this bootstrap cycle.
     *
     * @param string $source Either 'cache', 'disk', or 'closure'
     */
    public function markSource(string $source) : void
    {
        $this->source = $source;
    }

    /**
     * Get the source that was used for route loading.
     */
    public function getSource() : string|null
    {
        return $this->source;
    }

    /**
     * Reset the bootstrap state (primarily for testing).
     *
     * @internal Should only be used in test teardown scenarios
     */
    public function reset() : void
    {
        $this->booted = false;
        $this->source = null;
    }
}