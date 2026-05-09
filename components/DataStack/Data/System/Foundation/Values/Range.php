<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Foundation\Values;

use InvalidArgumentException;

/**
 * Range — a bounded range of comparable values.
 *
 * Supports inclusive and exclusive boundaries.
 */
final readonly class Range
{
    public function __construct(
        public mixed $start,
        public mixed $end,
        public bool  $startInclusive = true,
        public bool  $endInclusive = true,
    )
    {
        if ($start > $end) {
            throw new InvalidArgumentException(message: 'Range start must not exceed end.');
        }
    }

    public function intersects(self $other) : bool
    {
        return $this->contains($other->start) || $this->contains($other->end)
            || $other->contains($this->start) || $other->contains($this->end);
    }

    public function contains(mixed $value) : bool
    {
        if ($value < $this->start) {
            return false;
        }
        if (! $this->startInclusive && $value === $this->start) {
            return false;
        }

        if ($value > $this->end) {
            return false;
        }
        if (! $this->endInclusive && $value === $this->end) {
            return false;
        }

        return true;
    }

    /**
     * @return array{start: mixed, end: mixed, start_inclusive: bool, end_inclusive: bool}
     */
    public function toArray() : array
    {
        return [
            'start'           => $this->start,
            'end'             => $this->end,
            'start_inclusive' => $this->startInclusive,
            'end_inclusive'   => $this->endInclusive,
        ];
    }
}
