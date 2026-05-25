<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Foundation\Time;

use DateTimeImmutable;

final readonly class FrozenClock implements Clock
{
    public function __construct(private DateTimeImmutable $time) {}

    public function now(): DateTimeImmutable
    {
        return $this->time;
    }
}
