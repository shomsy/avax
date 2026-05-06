<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ComponentManifest;

final class ComponentDiscovery
{
    /**
     * @var array<string, ComponentManifest>
     */
    private array $components = [];

    /**
     * @param list<string> $searchPaths
     */
    public function discover(array $searchPaths = []) : void
    {
    }

    public function register(ComponentManifest $componentManifest) : void
    {
        $this->components[$componentManifest->name] = $componentManifest;
    }

    public function get(string $name): ?ComponentManifest
    {
        return $this->components[$name] ?? null;
    }

    /**
     * @return array<string, ComponentManifest>
     */
    public function all(): array
    {
        return $this->components;
    }

    /**
     * @return list<string>
     */
    public function detectMissingDependencies(): array
    {
        return [];
    }
}
