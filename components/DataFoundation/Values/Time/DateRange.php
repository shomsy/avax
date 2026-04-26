<?php

declare(strict_types=1);

namespace components\DataFoundation\Values\Time;

use components\DataFoundation\Exceptions\InvalidValueException;
use DateTimeImmutable;

/**
 * Inclusive immutable date range.
 */
final readonly class DateRange
{
    public function __construct(
        private DateTimeImmutable $start,
        private DateTimeImmutable $end,
    )
    {
        if ($start > $end) {
            throw InvalidValueException::because(message: 'Date range start must not be after date range end.');
        }
    }

    public function contains(DateTimeImmutable $value) : bool
    {
        return $value >= $this->start && $value <= $this->end;
    }

    public function start() : DateTimeImmutable
    {
        return $this->start;
    }

    public function end() : DateTimeImmutable
    {
        return $this->end;
    }
}
