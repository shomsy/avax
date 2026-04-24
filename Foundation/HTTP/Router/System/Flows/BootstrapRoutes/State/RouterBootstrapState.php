<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\System\Flows\BootstrapRoutes\State;

use RuntimeException;

/**
 * Thread-safe bootstrap state management for BootstrapRoutes.
 *
 * Replaces static state with instance-based state to ensure thread-safety
 * and proper isolation between different container instances.
 */
final class RouterBootstrapState
{
    public bool        $booted
        = false {
            get {
                return $this->booted;
            }
        }
    public string|null $source
        = null {
            get {
                return $this->source;
            }
        }

    /**
     * Ensure the bootstrapper has not already been booted.
     *
     * @throws RuntimeException If already bootstrapped
     */
    public function ensureNotBooted() : void
    {
        if ($this->booted) {
            throw new RuntimeException(message: 'Router bootstrapper has already been executed. Cannot bootstrap multiple times.');
        }
        $this->booted = true;
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