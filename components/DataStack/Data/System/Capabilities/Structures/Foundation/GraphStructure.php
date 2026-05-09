<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation;

interface GraphStructure extends DataStructure
{
    public function hasNode(int|string $node) : bool;

    public function hasEdge(int|string $from, int|string $to) : bool;

    /**
     * @return list<array-key>
     */
    public function neighborsOf(int|string $node) : array;
}
