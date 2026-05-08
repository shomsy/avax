<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\PublicSurface;

use Avax\Components\DataStack\Data\System\Capabilities\Structures\Linear\Sequence as StructureSequence;

/**
 * Sequence — ordered indexed values data structure public entry point.
 */
final class Sequence
{
    private function __construct()
    {
    }

    /**
     * @param  iterable<mixed>  $values
     */
    public static function make(iterable $values = []): StructureSequence
    {
        return new StructureSequence(items: $values);
    }
}
