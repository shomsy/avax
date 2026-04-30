<?php

declare(strict_types=1);

namespace Avax\Components\DependencyMap\System\PublicSurface;

use Avax\Components\DependencyMap\System\Capabilities\Graph\DependencyGraph;

final readonly class DependencyMap
{
    public function __construct(private DependencyGraph $graph) {}

    public function add(string $dependency, array $dependsOn = []) : void
    {
        $this->graph->add($dependency, $dependsOn);
    }

    public function remove(string $dependency) : void
    {
        $this->graph->remove($dependency);
    }

    public function dependsOn(string $dependency) : array
    {
        return $this->graph->dependsOn($dependency);
    }

    public function dependents(string $dependency) : array
    {
        return $this->graph->dependents($dependency);
    }

    public function cycles() : array
    {
        return $this->graph->detectCycles();
    }

    public function orphans() : array
    {
        return $this->graph->findOrphans();
    }

    public function exportMermaid() : string
    {
        return $this->graph->toMermaid();
    }
}
