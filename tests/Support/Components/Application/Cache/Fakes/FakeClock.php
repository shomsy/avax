<?php

declare(strict_types=1);

namespace Avax\Tests\Support\Components\Application\Cache\Fakes;

use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\Duration;
use Avax\Components\Application\Cache\System\Foundation\Time\FrozenClock;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;
use Override;

final class FakeClock implements Clock
{
    private Timestamp $timestamp;

    public function __construct(?Timestamp $timestamp = null)
    {
        $this->timestamp = $timestamp ?? Timestamp::now();
    }

    #[Override]
    public function now() : Timestamp
    {
        return $this->timestamp;
    }

    public function setTime(Timestamp $timestamp) : void
    {
        $this->timestamp = $timestamp;
    }

    public function advance(int $seconds) : void
    {
        $this->timestamp = $this->timestamp->add(
            duration: Duration::ofSeconds(seconds: $seconds),
        );
    }

    public function freeze() : FrozenClock
    {
        return new FrozenClock(timestamp: $this->timestamp);
    }
}
