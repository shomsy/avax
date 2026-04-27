<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Internal\Comparison;

/**
 * Normalized ordering direction.
 */
enum Ordering: int
{
    case Ascending  = 1;
    case Descending = -1;
}
