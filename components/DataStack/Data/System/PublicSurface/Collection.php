<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\PublicSurface;

/**
 * Collection — item/object pipeline DSL public entry point.
 *
 * Collection composes Arrhae internally for all array-backed pipeline operations.
 */
final class Collection
{
    private function __construct()
    {
    }

    /**
     * @param  iterable<array-key, mixed>  $items
     */
    public static function make(iterable $items = []): \Avax\Components\DataStack\Data\System\Capabilities\Forms\CollectionForm\Collection
    {
        return \Avax\Components\DataStack\Data\System\Capabilities\Forms\CollectionForm\Collection::make(items: $items);
    }
}
