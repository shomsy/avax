<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Values\Time;

use Avax\DataFoundation\Exceptions\InvalidValueException;

/**
 * Inclusive numeric range.
 */
final readonly class Range
{
    public function __construct(
        private int|float $start,
        private int|float $end,
    )
    {
        if ($start > $end) {
            throw InvalidValueException::because(message: 'Range start must be less than or equal to range end.');
        }
    }

    public function contains(int|float $value) : bool
    {
        return $value >= $this->start && $value <= $this->end;
    }

    public function start() : int|float
    {
        return $this->start;
    }

    public function end() : int|float
    {
        return $this->end;
    }
}
