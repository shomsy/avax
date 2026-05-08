<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\PublicSurface;

use Avax\Components\DataStack\Data\System\Capabilities\Structures\Maps\OrderedMap as StructureOrderedMap;

/**
 * OrderedMap — insertion-order key/value invariant data structure public entry point.
 */
final class OrderedMap
{
    private function __construct()
    {
    }

    /**
     * @param  iterable<string, mixed>  $entries
     */
    public static function make(iterable $entries = []): StructureOrderedMap
    {
        return new StructureOrderedMap(items: $entries);
    }
}
