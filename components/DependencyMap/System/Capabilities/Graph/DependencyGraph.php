<?php

declare(strict_types=1);

namespace Avax\Components\DependencyMap\System\Capabilities\Graph;

final class DependencyGraph
{
    /** @var array<string, DependencyNode> */
    private array $nodes = [];

    public function add(string $dependency, array $dependsOn = []) : void
    {
        $this->nodes[$dependency] = new DependencyNode($dependency, $dependsOn);
    }

    public function remove(string $dependency) : void
    {
        unset($this->nodes[$dependency]);
    }

    /**
     * @return list<string>
     */
    public function dependsOn(string $dependency) : array
    {
        return $this->nodes[$dependency]?->dependsOn ?? [];
    }

    /**
     * @return list<list<string>>
     */
    public function detectCycles() : array
    {
        $cycles = [];

        foreach ($this->nodes as $dependency => $node) {
            $path    = [];
            $visited = [];

            if ($this->findCycle($dependency, $path, $visited)) {
                $cycles[] = $path;
            }
        }

        return $cycles;
    }

    private function findCycle(string $dependency, array &$path, array &$visited) : bool
    {
        if (isset($visited[$dependency])) {
            return true;
        }

        if (isset($path[$dependency])) {
            $path[] = $dependency;

            return true;
        }

        if (! isset($this->nodes[$dependency])) {
            return false;
        }

        $path[$dependency]    = $dependency;
        $visited[$dependency] = true;

        foreach ($this->nodes[$dependency]->dependsOn as $dep) {
            if ($this->findCycle($dep, $path, $visited)) {
                return true;
            }
        }

        unset($path[$dependency]);

        return false;
    }

    /**
     * @return list<string>
     */
    public function findOrphans() : array
    {
        $orphans = [];

        foreach ($this->nodes as $node) {
            if (empty($node->dependsOn) && empty($this->dependents($node->name))) {
                $orphans[] = $node->name;
            }
        }

        return $orphans;
    }

    /**
     * @return list<string>
     */
    public function dependents(string $dependency) : array
    {
        $dependents = [];

        foreach ($this->nodes as $node) {
            if (in_array($dependency, $node->dependsOn, true)) {
                $dependents[] = $node->name;
            }
        }

        return $dependents;
    }

    public function toMermaid() : string
    {
        $lines = ['graph TD'];

        foreach ($this->nodes as $node) {
            foreach ($node->dependsOn as $dep) {
                $lines[] = "    {$node->name} --> {$dep}";
            }
        }

        return implode("\n", $lines);
    }
}

final readonly class DependencyNode
{
    /**
     * @param list<string> $dependsOn
     */
    public function __construct(
        public string $name,
        public array  $dependsOn = [],
    ) {}
}
