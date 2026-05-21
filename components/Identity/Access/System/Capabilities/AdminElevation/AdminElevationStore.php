<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities\AdminElevation;

/**
 * Simple in-memory store for admin elevation state.
 *
 * Worker-safe: must be reset per request via ElevationReset.
 */
final class AdminElevationStore
{
    private bool $elevated = false;

    public function elevate() : void
    {
        $this->elevated = true;
    }

    public function isActive() : bool
    {
        return $this->elevated;
    }

    public function reset() : void
    {
        $this->elevated = false;
    }
}
