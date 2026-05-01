<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ComponentManifest;

final class ComponentDiscovery
{
    /**
     * @var array<string, ComponentManifest>
     */
    private array $components = [];

    public function discover(string $path): self
    {
        return $this;
    }

    public function register(ComponentManifest $manifest): void
    {
        $this->components[$manifest->name] = $manifest;
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
}
