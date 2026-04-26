<?php

declare(strict_types=1);

namespace components\Cache\System\Foundation\Time;

final class FrozenClock implements Clock
{
    private Timestamp $frozenAt;

    public function __construct(Timestamp|null $timestamp = null)
    {
        $this->frozenAt = $timestamp ?? Timestamp::now();
    }

    public function now() : Timestamp
    {
        return $this->frozenAt;
    }

    public function moveForward(Duration $duration) : void
    {
        $this->frozenAt = $this->frozenAt->add(duration: $duration);
    }

    public function reset(Timestamp|null $timestamp = null) : void
    {
        $this->frozenAt = $timestamp ?? Timestamp::now();
    }
}