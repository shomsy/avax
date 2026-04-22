<?php

declare(strict_types=1);

namespace Avax\Auth\System\Foundation\Time;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class TimeWindow
{
    public DateTimeImmutable $startsAt;
    public DateTimeImmutable $endsAt;

    public function __construct(DateTimeImmutable $startsAt, DateTimeImmutable $endsAt)
    {
        if ($endsAt < $startsAt) {
            throw new InvalidArgumentException(message: 'TimeWindow end must be greater than or equal to start.');
        }

        $this->startsAt = $startsAt;
        $this->endsAt   = $endsAt;
    }

    public function contains(DateTimeImmutable $moment) : bool
    {
        return $moment >= $this->startsAt && $moment <= $this->endsAt;
    }
}
