<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\PublicSurface;

use Avax\Components\DataStack\Data\System\Capabilities\Structures\Sets\OrderedSet as StructureOrderedSet;

/**
 * OrderedSet — insertion-order uniqueness invariant data structure public entry point.
 */
final class OrderedSet
{
    private function __construct()
    {
    }

    /**
     * @param  iterable<mixed>  $values
     */
    public static function make(iterable $values = []): StructureOrderedSet
    {
        return new StructureOrderedSet(items: $values);
    }
}
