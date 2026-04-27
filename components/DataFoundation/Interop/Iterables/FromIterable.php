<?php

declare(strict_types=1);

namespace components\DataFoundation\Interop\Iterables;

use components\DataFoundation\Collection;
use components\DataFoundation\Collections\DataList\DataList;
use components\DataFoundation\Collections\Sequence\Sequence;

/**
 * Iterable entry points into ordered DataFoundation types.
 */
final readonly class FromIterable
{
    public static function toCollection(iterable $items) : Collection
    {
        return Collection::make(items: $items);
    }

    public static function toDataList(iterable $items) : DataList
    {
        return new DataList(items: $items);
    }

    public static function toSequence(iterable $items) : Sequence
    {
        return new Sequence(items: $items);
    }
}
