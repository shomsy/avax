<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\PublicSurface;

use Avax\Components\DataStack\Database\System\Capabilities\ORM\EntityManager as EntitiesCapability;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Metadata\EntityMetadata;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Repositories\EntityRepository;

/**
 * Entities - Public surface for the Entity Persistence (ORM).
 * Renamed from EntityManager to comply with "No Managers" rule.
 */
final readonly class Entities
{
    public function __construct(
        private EntitiesCapability $entitiesCapability,
    ) {}

    public function metadata(string $entityClass): EntityMetadata
    {
        return $this->entitiesCapability->metadata($entityClass);
    }

    public function find(string $entityClass, mixed $id, ?string $connectionName = null) : ?object
    {
        return $this->entitiesCapability->find($entityClass, $id, $connectionName);
    }

    public function persist(object $entity): void
    {
        $this->entitiesCapability->persist($entity);
    }

    public function remove(object $entity): void
    {
        $this->entitiesCapability->remove($entity);
    }

    public function flush(?string $connectionName = null) : void
    {
        $this->entitiesCapability->flush($connectionName);
    }

    public function clear(): void
    {
        $this->entitiesCapability->clear();
    }

    public function refresh(object $entity, ?string $connectionName = null) : object
    {
        return $this->entitiesCapability->refresh($entity, $connectionName);
    }

    public function repository(string $entityClass): EntityRepository
    {
        return $this->entitiesCapability->repository($entityClass);
    }

    public function transactional(callable $callback, ?string $connectionName = null) : mixed
    {
        return $this->entitiesCapability->transactional($callback, $connectionName);
    }
}
