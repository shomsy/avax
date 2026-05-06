<?php

declare(strict_types=1);

namespace Avax\Components\Database\System\PublicSurface;

use Avax\Components\Database\System\Capabilities\ORM\EntityManager as EntityManagerCapability;
use Avax\Components\Database\System\Capabilities\ORM\Metadata\EntityMetadata;
use Avax\Components\Database\System\Capabilities\ORM\Repositories\EntityRepository;

/**
 * Public surface for the Entity Manager (ORM).
 */
final readonly class EntityManager
{
    public function __construct(
        private EntityManagerCapability $entityManager
    ) {
    }

    public function metadata(string $entityClass): EntityMetadata
    {
        return $this->entityManager->metadata($entityClass);
    }

    public function find(string $entityClass, mixed $id, ?string $connectionName = null): ?object
    {
        return $this->entityManager->find($entityClass, $id, $connectionName);
    }

    public function persist(object $entity): void
    {
        $this->entityManager->persist($entity);
    }

    public function remove(object $entity): void
    {
        $this->entityManager->remove($entity);
    }

    public function flush(?string $connectionName = null): void
    {
        $this->entityManager->flush($connectionName);
    }

    public function clear(): void
    {
        $this->entityManager->clear();
    }

    public function refresh(object $entity, ?string $connectionName = null): object
    {
        return $this->entityManager->refresh($entity, $connectionName);
    }

    public function repository(string $entityClass): EntityRepository
    {
        return $this->entityManager->repository($entityClass);
    }

    public function transactional(callable $callback, ?string $connectionName = null): mixed
    {
        return $this->entityManager->transactional($callback, $connectionName);
    }
}
