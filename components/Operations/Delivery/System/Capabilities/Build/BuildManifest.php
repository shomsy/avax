<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Delivery\System\Capabilities\Build;

class BuildManifest
{
    /**
     * @var array<int, array{path: string, type: string}>
     */
    private array $artifacts = [];

    /**
     * @var array<string, string>
     */
    private array $checksums = [];

    public function __construct(private readonly string $env = 'production')
    {
    }

    public function addArtifact(string $path, string $type): self
    {
        $this->artifacts[] = ['path' => $path, 'type' => $type];

        return $this;
    }

    /**
     * @return array<int, array{path: string, type: string}>
     */
    public function getArtifacts(): array
    {
        return $this->artifacts;
    }

    public function getChecksum(string $path): ?string
    {
        return $this->checksums[$path] ?? null;
    }

    /**
     * @return array{env: string, artifacts: array<int, array{path: string, type: string}>, timestamp: string}
     */
    public function generate(): array
    {
        return [
            'env' => $this->env,
            'artifacts' => $this->artifacts,
            'timestamp' => date('c'),
        ];
    }
}
