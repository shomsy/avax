<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Interop\Arrays;

use Traversable;

/**
 * Array conversion boundary for public DataFoundation values.
 */
final readonly class ToArray
{
    /**
     * @return array<mixed>
     */
    public static function from(mixed $value) : array
    {
        return match (true) {
            is_array($value)                                      => $value,
            $value instanceof Traversable                         => iterator_to_array($value, true),
            is_object($value) && method_exists($value, 'toArray') => $value->toArray(),
            is_object($value) && method_exists($value, 'all')     => $value->all(),
            default                                               => [$value],
        };
    }
}
