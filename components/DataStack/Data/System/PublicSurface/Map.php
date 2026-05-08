<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\PublicSurface;

use Avax\Components\DataStack\Data\System\Capabilities\Structures\Maps\Map as StructureMap;

/**
 * Map — key/value invariant data structure public entry point.
 */
final class Map
{
    private function __construct()
    {
    }

    /**
     * @param  iterable<string, mixed>  $entries
     */
    public static function make(iterable $entries = []): StructureMap
    {
        return new StructureMap(items: $entries);
    }
}
