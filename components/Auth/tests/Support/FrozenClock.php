<?php

declare(strict_types=1);

namespace components\Auth\Tests\Support;

use components\Auth\System\Foundation\Clock;
use DateInterval;
use DateTimeImmutable;
use Override;

/**
 * Deterministic clock for time-sensitive auth tests.
 */
final class FrozenClock extends Clock
{
    private DateTimeImmutable $now;

    public function __construct(
        DateTimeImmutable $now
    )
    {
        $this->now = $now;
    }

    #[Override]
    public function now() : DateTimeImmutable
    {
        return $this->now;
    }

    #[Override]
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
        $this->now = $this->now->add(interval: $interval);
    }
}
