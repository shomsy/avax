<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\PublicSurface;

use Avax\Components\DataStack\Data\System\Capabilities\Structures\Linear\Stack as StructureStack;

final class Stack
{
    private function __construct() {}

    /**
     * @param iterable<mixed> $values
     */
    public static function make(iterable $values = []) : StructureStack
    {
        return new StructureStack(items: $values);
    }
}
