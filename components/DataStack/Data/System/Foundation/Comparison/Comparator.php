<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Foundation\Comparison;

/**
 * Comparator capability for ordering values.
 *
 * Provides a three-way comparison result: -1 (less), 0 (equal), 1 (greater).
 */
interface Comparator
{
    /**
     * Compare two values and return -1, 0, or 1.
     */
    public function compare(mixed $left, mixed $right) : int;
}
