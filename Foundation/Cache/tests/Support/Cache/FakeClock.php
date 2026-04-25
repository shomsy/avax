<?php

declare(strict_types=1);

namespace Avax\Cache\Tests\Support\Cache;

use Avax\Cache\System\Foundation\Time\Clock;
use Avax\Cache\System\Foundation\Time\Duration;
use Avax\Cache\System\Foundation\Time\FrozenClock;
use Avax\Cache\System\Foundation\Time\Timestamp;

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
            Duration::ofSeconds($seconds)
        );
    }

    public function freeze() : FrozenClock
    {
        return new FrozenClock($this->currentTime);
    }
}