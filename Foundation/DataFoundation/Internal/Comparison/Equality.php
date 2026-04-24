<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Internal\Comparison;

/**
 * Defines strict equality semantics for DataFoundation values.
 */
final readonly class Equality
{
    public static function same(mixed $left, mixed $right) : bool
    {
        return Comparator::hash(value: $left) === Comparator::hash(value: $right);
    }
}
