<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Interop\Arrays;

use Avax\DataFoundation\Arrhae;
use Avax\DataFoundation\Collection;
use Avax\DataFoundation\Collections\DataList\DataList;
use Avax\DataFoundation\Collections\Map\Map;

/**
 * Array entry points into public DataFoundation types.
 */
final readonly class FromArray
{
    public static function toArrhae(array $items) : Arrhae
    {
        return new Arrhae(items: $items);
    }

    public static function toCollection(array $items) : Collection
    {
        return new Collection(items: $items);
    }

    public static function toDataList(array $items) : DataList
    {
        return new DataList(items: $items);
    }

    public static function toMap(array $items) : Map
    {
        return new Map(items: $items);
    }
}
