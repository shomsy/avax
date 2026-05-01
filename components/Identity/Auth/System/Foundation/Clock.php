<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Foundation;

use DateTimeImmutable;

/**
 * Clock service for time-related operations.
 */
class Clock
{
    /**
     * Get the current time.
     */
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    /**
     * Get current timestamp.
     */
    public function timestamp(): int
    {
        return time();
    }
}
