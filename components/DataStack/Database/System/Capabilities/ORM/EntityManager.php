<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\ORM;

use Avax\Components\DataStack\Database\System\Capabilities\ORM\Persisters\EntityPersister;

/**
 * Entity manager providing basic persistence operations.
 *
 * This is the canonical ORM entry point. It delegates to EntityPersister
 * for actual persistence behavior with lifecycle event integration.
 *
 * Use the DataStack Database public surface API (onEntity/onQuery/onTransaction)
 * for lifecycle-aware operations, or use this class for direct persistence.
 */
final readonly class EntityManager
{
    public function __construct(
        private EntityPersister $persister,
    ) {
    }

    /**
     * Persist a new entity.
     *
     * @throws \Throwable
     */
    public function persist(object $entity, string|null $connectionName = null): void
    {
        $this->persister->insert(entity: $entity, connectionName: $connectionName);
    }

    /**
     * Update an existing entity.
     *
     * @throws \Throwable
     */
    public function update(object $entity, string|null $connectionName = null): void
    {
        $this->persister->update(entity: $entity, connectionName: $connectionName);
    }

    /**
     * Remove an entity.
     *
     * @throws \Throwable
     */
    public function remove(object $entity, string|null $connectionName = null): void
    {
        $this->persister->delete(entity: $entity, connectionName: $connectionName);
    }

    /**
     * Refresh an entity from the database.
     *
     * @throws \Throwable
     */
    public function refresh(object $entity, string|null $connectionName = null): object
    {
        return $this->persister->refresh(entity: $entity, connectionName: $connectionName);
    }

    /**
     * Find an entity by its identifier.
     *
     * @param  class-string  $entityClass
     *
     * @throws \Throwable
     */
    public function find(string $entityClass, mixed $id, string|null $connectionName = null): ?object
    {
        return $this->persister->find(entityClass: $entityClass, id: $id, connectionName: $connectionName);
    }

    /**
     * Find all entities of a given class.
     *
     * @param  class-string  $entityClass
     * @return list<object>
     *
     * @throws \Throwable
     */
    public function findAll(string $entityClass, string|null $connectionName = null): array
    {
        return $this->persister->findAll(entityClass: $entityClass, connectionName: $connectionName);
    }
}
