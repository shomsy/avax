<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Foundation\Time;

use DateTimeImmutable;

final class NativeClock implements Clock
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now');
    }
}
