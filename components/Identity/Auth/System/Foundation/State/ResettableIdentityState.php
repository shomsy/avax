<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Foundation\State;

/**
 * ResettableIdentityState — contract for in-memory state that must be cleared
 * between requests in long-lived runtimes (RoadRunner, Swoole, Workerman, FrankenPHP).
 *
 * Adapted from the enterprise reference package.
 * All InMemory store implementations in the Identity component must implement this interface.
 */
interface ResettableIdentityState
{
    /**
     * Clears request/runtime-local mutable state.
     *
     * Long-lived runtimes must call this between requests when this implementation
     * is used as in-memory state.
     */
    public function reset(): void;
}
