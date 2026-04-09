<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Support;

use Avax\Auth\System\Foundation\Clock;
use DateInterval;
use DateTimeImmutable;

/**
 * Deterministic clock for time-sensitive auth tests.
 */
final class FrozenClock extends Clock
{
    public function __construct(
        private DateTimeImmutable $now
    ) {}

    public function now() : DateTimeImmutable
    {
        return $this->now;
    }

    public function timestamp() : int
    {
        return $this->now->getTimestamp();
    }

    public function set(DateTimeImmutable $moment) : void
    {
        $this->now = $moment;
    }

    public function advance(DateInterval $interval) : void
    {
        $this->now = $this->now->add($interval);
    }
}
