<?php

declare(strict_types=1);

namespace Avax\Components\ServiceMap\System\PublicSurface;

use Avax\Components\ServiceMap\System\Capabilities\Graph\ServiceGraph;
use Avax\Components\ServiceMap\System\Capabilities\Graph\ServiceNode;

final class ServiceMap
{
    private static ServiceGraph $graph;

    public static function add(string $service, array $dependsOn = []) : void
    {
        self::graph()->add($service, $dependsOn);
    }

    private static function graph() : ServiceGraph
    {
        if (! isset(self::$graph)) {
            self::$graph = new ServiceGraph();
        }

        return self::$graph;
    }

    public static function remove(string $service) : void
    {
        self::graph()->remove($service);
    }

    public static function dependsOn(string $service) : array
    {
        return self::graph()->dependsOn($service);
    }

    public static function dependents(string $service) : array
    {
        return self::graph()->dependents($service);
    }

    public static function cycles() : array
    {
        return self::graph()->detectCycles();
    }

    public static function orphans() : array
    {
        return self::graph()->findOrphans();
    }

    public static function exportMermaid() : string
    {
        return self::graph()->toMermaid();
    }
}