<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\PublicSurface;

use Avax\Components\DataStack\Data\System\Capabilities\Structures\Linear\Deque as StructureDeque;

final class Deque
{
    private function __construct() {}

    /**
     * @param iterable<mixed> $values
     */
    public static function make(iterable $values = []) : StructureDeque
    {
        return new StructureDeque(items: $values);
    }
}
