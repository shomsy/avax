<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\PublicSurface;

use Avax\Components\DataStack\Database\System\Capabilities\ORM\Entities as EntitiesCapability;
use Throwable;

/**
 * Public surface for entity persistence.
 */
final readonly class Entities
{
    public function __construct(
        private EntitiesCapability $entitiesCapability,
    ) {
    }

    /**
     * @param  class-string  $entityClass
     */
    public function find(string $entityClass, mixed $id, ?string $connectionName = null): ?object
    {
        return $this->entitiesCapability->find(
            entityClass: $entityClass,
            id: $id,
            connection: $connectionName,
        );
    }

    /**
     * @param  class-string  $entityClass
     * @param  array<string, mixed>  $criteria
     * @return list<object>
     */
    public function findBy(
        string $entityClass,
        array $criteria,
        ?string $orderBy = null,
        ?string $direction = null,
        ?int $limit = null,
        ?int $offset = null,
        ?string $connectionName = null,
    ): array {
        return $this->entitiesCapability->findBy(
            entityClass: $entityClass,
            criteria: $criteria,
            orderBy: $orderBy,
            direction: $direction,
            limit: $limit,
            offset: $offset,
            connection: $connectionName,
        );
    }

    /**
     * @throws Throwable
     */
    public function insert(object $entity, ?string $connectionName = null): void
    {
        $this->entitiesCapability->insert(entity: $entity, connection: $connectionName);
    }

    /**
     * Alias kept for a natural public persistence API.
     *
     * @throws Throwable
     */
    public function persist(object $entity, ?string $connectionName = null): void
    {
        $this->insert(entity: $entity, connectionName: $connectionName);
    }

    /**
     * @throws Throwable
     */
    public function update(object $entity, ?string $connectionName = null): void
    {
        $this->entitiesCapability->update(entity: $entity, connection: $connectionName);
    }

    /**
     * @throws Throwable
     */
    public function delete(object $entity, ?string $connectionName = null): void
    {
        $this->entitiesCapability->delete(entity: $entity, connection: $connectionName);
    }

    /**
     * Alias kept for a natural public persistence API.
     *
     * @throws Throwable
     */
    public function remove(object $entity, ?string $connectionName = null): void
    {
        $this->delete(entity: $entity, connectionName: $connectionName);
    }

    /**
     * @throws Throwable
     */
    public function refresh(object $entity, ?string $connectionName = null): object
    {
        return $this->entitiesCapability->refresh(entity: $entity, connection: $connectionName);
    }
}
