<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Foundation\Values;

/**
 * Interval — a numeric range with length and midpoint calculations.
 */
final readonly class Interval
{
    public function __construct(
        public float|int $start,
        public float|int $end,
    ) {}

    public function midpoint() : float
    {
        return ($this->start + $this->end) / 2.0;
    }

    public function contains(float|int $value) : bool
    {
        return $value >= $this->start && $value <= $this->end;
    }

    public function overlaps(self $other) : bool
    {
        return $this->start <= $other->end && $other->start <= $this->end;
    }

    /**
     * @return array{start: float|int, end: float|int, length: float|int}
     */
    public function toArray() : array
    {
        return ['start' => $this->start, 'end' => $this->end, 'length' => $this->length()];
    }

    public function length() : float|int
    {
        return $this->end - $this->start;
    }
}
