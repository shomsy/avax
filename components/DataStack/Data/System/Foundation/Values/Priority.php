<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Foundation\Values;

/**
 * Priority — a named priority value for heap and queue ordering.
 *
 * Lower numeric values represent higher priority (closer to the front).
 */
final readonly class Priority
{
    public function __construct(
        public float|int $value,
        public string    $label = '',
    ) {}

    public function isHigherThan(self $other) : bool
    {
        return $this->value < $other->value;
    }

    public function isLowerThan(self $other) : bool
    {
        return $this->value > $other->value;
    }

    public function compareTo(self $other) : int
    {
        return $this->value <=> $other->value;
    }
}
