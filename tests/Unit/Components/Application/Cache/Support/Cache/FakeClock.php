<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Cache\Support\Cache;

use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\Duration;
use Avax\Components\Application\Cache\System\Foundation\Time\FrozenClock;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;

final class FakeClock implements Clock
{
    private Timestamp $currentTime;

    public function __construct(?Timestamp $timestamp = null)
    {
        $this->currentTime = $timestamp ?? Timestamp::now();
    }

    public function now() : Timestamp
    {
        return $this->currentTime;
    }

    public function setTime(Timestamp $timestamp) : void
    {
        $this->currentTime = $timestamp;
    }

    public function advance(int $seconds) : void
    {
        $this->currentTime = $this->currentTime->add(
            duration: Duration::ofSeconds(seconds: $seconds),
        );
    }

    public function freeze() : FrozenClock
    {
        return new FrozenClock(timestamp: $this->currentTime);
    }
}
