<?php

declare(strict_types=1);

namespace Avax\Components\DependencyMap\System\PublicSurface;

use Avax\Components\DependencyMap\System\Capabilities\Graph\DependencyGraph;

final readonly class DependencyMap
{
    public function __construct(private DependencyGraph $dependencyGraph)
    {
    }

    /**
     * @param  list<string>  $dependsOn
     */
    public function add(string $dependency, array $dependsOn = []): void
    {
        $this->dependencyGraph->add($dependency, $dependsOn);
    }

    public function remove(string $dependency): void
    {
        $this->dependencyGraph->remove($dependency);
    }

    /**
     * @return list<string>
     */
    public function dependsOn(string $dependency): array
    {
        return $this->dependencyGraph->dependsOn($dependency);
    }

    /**
     * @return list<string>
     */
    public function dependents(string $dependency): array
    {
        return $this->dependencyGraph->dependents($dependency);
    }

    /**
     * @return list<list<string>>
     */
    public function cycles(): array
    {
        return $this->dependencyGraph->detectCycles();
    }

    /**
     * @return list<string>
     */
    public function orphans(): array
    {
        return $this->dependencyGraph->findOrphans();
    }

    public function exportMermaid(): string
    {
        return $this->dependencyGraph->toMermaid();
    }
}
