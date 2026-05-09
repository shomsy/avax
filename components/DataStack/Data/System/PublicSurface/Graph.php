<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\PublicSurface;

use Avax\Components\DataStack\Data\System\Capabilities\Structures\Graphs\Graph as StructureGraph;

final class Graph
{
    private function __construct() {}

    public static function directed() : StructureGraph
    {
        return StructureGraph::directed();
    }

    public static function undirected() : StructureGraph
    {
        return StructureGraph::undirected();
    }
}
