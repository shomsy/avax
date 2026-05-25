<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Foundation;

use Avax\Components\Identity\Auth\System\Foundation\Time\ClockInterface;
use DateTimeImmutable;

/**
 * Clock service for time-related operations.
 */
class Clock implements ClockInterface
{
    /**
     * Get the current time.
     */
    public function now() : DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    /**
     * Get current timestamp.
     */
    public function timestamp() : int
    {
        return time();
    }
}
