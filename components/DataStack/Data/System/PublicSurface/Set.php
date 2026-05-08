<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\PublicSurface;

use Avax\Components\DataStack\Data\System\Capabilities\Structures\Sets\Set as StructureSet;

/**
 * Set — uniqueness invariant data structure public entry point.
 */
final class Set
{
    private function __construct()
    {
    }

    /**
     * @param  iterable<mixed>  $values
     */
    public static function make(iterable $values = []): StructureSet
    {
        return new StructureSet(items: $values);
    }
}
