<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Foundation\Comparison;

/**
 * Ordering capability for sortable values.
 *
 * A value that knows its own position relative to other values of the same type.
 */
interface Ordering
{
    /**
     * Compare this value to another and return -1, 0, or 1.
     */
    public function compareTo(mixed $other) : int;
}
