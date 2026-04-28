<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Collections\Internal\Iteration;

/**
 * Normalizes various iterable inputs to array.
 */
final readonly class NormalizedIterable
{
    /**
     * @param iterable<mixed> $iterable
     *
     * @return array<mixed>
     */
    public static function toArray(iterable $iterable) : array
    {
        if (is_array(value: $iterable)) {
            return $iterable;
        }

        return iterator_to_array(iterator: $iterable, preserve_keys: false);
    }

    /**
     * @param iterable<mixed> $iterable
     *
     * @return array<mixed>
     */
    public static function toArrayPreserveKeys(iterable $iterable) : array
    {
        if (is_array(value: $iterable)) {
            return $iterable;
        }

        return iterator_to_array(iterator: $iterable, preserve_keys: true);
    }
}
