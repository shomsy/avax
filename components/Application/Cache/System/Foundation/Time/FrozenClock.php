<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Foundation\Time;

use Override;

final class FrozenClock implements Clock
{
    private Timestamp $timestamp;

    public function __construct(Timestamp $timestamp = null)
    {
        $this->timestamp = $timestamp ?? Timestamp::now();
    }

    #[Override]
    public function now() : Timestamp
    {
        return $this->timestamp;
    }

    public function moveForward(Duration $duration) : void
    {
        $this->timestamp = $this->timestamp->add(duration: $duration);
    }

    public function reset(Timestamp $timestamp = null) : void
    {
        $this->timestamp = $timestamp ?? Timestamp::now();
    }
}
