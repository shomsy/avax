<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\Dx\System\Capabilities\PublicSurface;

use Avax\Components\DeveloperTools\Dx\System\Capabilities\Capabilities\Graph\DependencyGraph;

final readonly class DependencyMap
{
    public function __construct(private DependencyGraph $dependencyGraph) {}

    public function add(string $dependency, array $dependsOn = []) : void
    {
        $this->dependencyGraph->add($dependency, $dependsOn);
    }

    public function remove(string $dependency) : void
    {
        $this->dependencyGraph->remove($dependency);
    }

    public function dependsOn(string $dependency) : array
    {
        return $this->dependencyGraph->dependsOn($dependency);
    }

    public function dependents(string $dependency) : array
    {
        return $this->dependencyGraph->dependents($dependency);
    }

    public function cycles() : array
    {
        return $this->dependencyGraph->detectCycles();
    }

    public function orphans() : array
    {
        return $this->dependencyGraph->findOrphans();
    }

    public function exportMermaid() : string
    {
        return $this->dependencyGraph->toMermaid();
    }
}
