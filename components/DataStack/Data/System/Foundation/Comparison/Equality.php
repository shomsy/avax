<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Foundation\Comparison;

/**
 * Equality capability for value comparison.
 *
 * Determines whether two values are equal according to the chosen strategy.
 */
interface Equality
{
    /**
     * Check whether two values are equal.
     */
    public function equals(mixed $left, mixed $right) : bool;
}
