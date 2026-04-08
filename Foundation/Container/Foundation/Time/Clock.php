<?php

declare(strict_types=1);

namespace Avax\Container\Foundation\Time;

/**
 * Provides the container-owned time source.
 */
final class Clock
{
    /**
     * Returns the current timestamp as a float.
     */
    public function now() : float
    {
        return microtime(as_float: true);
    }
}
