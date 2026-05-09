<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\PublicSurface;

use Avax\Components\DataStack\Data\System\Capabilities\Structures\Priority\PriorityQueue as StructurePriorityQueue;

final class PriorityQueue
{
    private function __construct() {}

    public static function min() : StructurePriorityQueue
    {
        return StructurePriorityQueue::min();
    }

    public static function max() : StructurePriorityQueue
    {
        return StructurePriorityQueue::max();
    }
}
