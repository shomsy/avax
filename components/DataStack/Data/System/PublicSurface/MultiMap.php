<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\PublicSurface;

use Avax\Components\DataStack\Data\System\Capabilities\Structures\Maps\MultiMap as StructureMultiMap;

/**
 * MultiMap — one-to-many key/value invariant data structure public entry point.
 */
final class MultiMap
{
    private function __construct()
    {
    }

    /**
     * @param  iterable<string, iterable<mixed>>  $entries
     */
    public static function make(iterable $entries = []): StructureMultiMap
    {
        return new StructureMultiMap(items: $entries);
    }
}
