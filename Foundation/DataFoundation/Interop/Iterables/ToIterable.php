<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Interop\Iterables;

use ArrayIterator;
use Avax\DataFoundation\Interop\Arrays\ToArray;
use Traversable;

/**
 * Converts public values into iterable form.
 */
final readonly class ToIterable
{
    public static function from(mixed $value) : iterable
    {
        if ($value instanceof Traversable) {
            return $value;
        }

        return new ArrayIterator(ToArray::from(value: $value));
    }
}
