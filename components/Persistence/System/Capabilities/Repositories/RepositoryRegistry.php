<?php

declare(strict_types=1);

namespace Avax\Components\Persistence\System\Capabilities\Repositories;

final class RepositoryRegistry
{
    /**
     * @var array<string, RepositoryBackend>
     */
    private array $backends = [];

    public function register(string $entityName, RepositoryBackend $backend): void
    {
        $this->backends[$entityName] = $backend;
    }

    public function get(string $entityName): RepositoryBackend
    {
        return $this->backends[$entityName] ?? throw new \RuntimeException(
            message: "No backend registered for entity: {$entityName}",
        );
    }

    public function has(string $entityName): bool
    {
        return isset($this->backends[$entityName]);
    }
}