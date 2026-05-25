<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Foundation\Time;

use DateTimeImmutable;

/**
 * FrozenClock — fixed-time clock for deterministic testing.
 *
 * Adapted from the enterprise reference package.
 * Returns the same time on every call until replaced.
 */
final readonly class FrozenClock implements ClockInterface
{
    public function __construct(private DateTimeImmutable $time) {}

    public function now(): DateTimeImmutable
    {
        return $this->time;
    }
}
