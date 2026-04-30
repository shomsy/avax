<?php

declare(strict_types=1);

namespace Avax\Components\ServiceMap\System\Capabilities\Graph;

final class ServiceGraph
{
    /** @var array<string, ServiceNode> */
    private array $nodes = [];

    public function add(string $service, array $dependsOn = []) : void
    {
        $this->nodes[$service] = new ServiceNode($service, $dependsOn);
    }

    public function remove(string $service) : void
    {
        unset($this->nodes[$service]);
    }

    /**
     * @return list<string>
     */
    public function dependsOn(string $service) : array
    {
        return $this->nodes[$service]?->dependsOn ?? [];
    }

    /**
     * @return list<list<string>>
     */
    public function detectCycles() : array
    {
        $cycles = [];

        foreach ($this->nodes as $service => $node) {
            $path    = [];
            $visited = [];

            if ($this->findCycle($service, $path, $visited)) {
                $cycles[] = $path;
            }
        }

        return $cycles;
    }

    private function findCycle(string $service, array &$path, array &$visited) : bool
    {
        if (isset($visited[$service])) {
            return true;
        }

        if (isset($path[$service])) {
            $path[] = $service;

            return true;
        }

        if (! isset($this->nodes[$service])) {
            return false;
        }

        $path[$service]    = $service;
        $visited[$service] = true;

        foreach ($this->nodes[$service]->dependsOn as $dep) {
            if ($this->findCycle($dep, $path, $visited)) {
                return true;
            }
        }

        unset($path[$service]);

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
    public function dependents(string $service) : array
    {
        $dependents = [];

        foreach ($this->nodes as $node) {
            if (in_array($service, $node->dependsOn, true)) {
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

final readonly class ServiceNode
{
    /**
     * @param list<string> $dependsOn
     */
    public function __construct(
        public string $name,
        public array  $dependsOn = [],
    ) {}
}