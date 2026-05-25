<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Foundation\State;

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
