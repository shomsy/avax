<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\PublicSurface;

use Avax\Components\DataStack\Data\System\Capabilities\Structures\Priority\BinaryHeap;

final class Heap
{
    private function __construct() {}

    public static function min() : BinaryHeap
    {
        return BinaryHeap::min();
    }

    public static function max() : BinaryHeap
    {
        return BinaryHeap::max();
    }
}
