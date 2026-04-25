<?php

declare(strict_types=1);

namespace Avax\Cache\System\Foundation\Time;

final class FrozenClock implements Clock
{
    private Timestamp $frozenAt;

    public function __construct(?Timestamp $timestamp = null)
    {
        $this->frozenAt = $timestamp ?? Timestamp::now();
    }

    public function now() : Timestamp
    {
        return $this->frozenAt;
    }

    public function moveForward(Duration $duration) : void
    {
        $this->frozenAt = $this->frozenAt->add($duration);
    }

    public function reset(?Timestamp $timestamp = null) : void
    {
        $this->frozenAt = $timestamp ?? Timestamp::now();
    }
}