<?php

declare(strict_types=1);

namespace Avax\Framework\System\Foundation\Time;

use DateTimeImmutable;

final readonly class FrozenClock implements Clock
{
    public function __construct(
        private DateTimeImmutable $frozenTime,
    ) {
    }

    public function now(): DateTimeImmutable
    {
        return $this->frozenTime;
    }

    public function withFrozenTime(DateTimeImmutable $newTime): self
    {
        return new self(frozenTime: $newTime);
    }
}