<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\PersistenceRepositories;

use RuntimeException;

/**
 * Registry for repository storage backends.
 *
 * Maps entity class names to their concrete RepositoryStorageInterface
 * implementations, enabling the framework to resolve the correct
 * storage backend for any entity type.
 */
final class RepositoryRegistry
{
    /** @var array<string, RepositoryStorageInterface> */
    private array $backends = [];

    public function register(string $entityClass, RepositoryStorageInterface $repositoryStorage): void
    {
        $this->backends[$entityClass] = $repositoryStorage;
    }

    public function get(string $entityClass): RepositoryStorageInterface
    {
        return $this->backends[$entityClass] ?? throw new RuntimeException(
            message: 'No storage backend registered for entity: '.$entityClass,
        );
    }

    public function has(string $entityClass): bool
    {
        return isset($this->backends[$entityClass]);
    }

    /**
     * Get all registered entity classes.
     *
     * @return array<string>
     */
    public function registeredEntities(): array
    {
        return array_keys($this->backends);
    }

    /**
     * Clear all registrations.
     * Useful for worker state reset.
     */
    public function clear(): void
    {
        $this->backends = [];
    }
}
