<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\ORM;

use Avax\Components\DataStack\Database\System\Capabilities\ORM\Hydration\Hydrator;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Metadata\AttributeMetadataReader;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Persisters\EntityPersister;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Query;
use Throwable;

/**
 * Coordinates entity lifecycle: find, persist, update, delete, and refresh.
 */
readonly class Entities
{
    public function __construct(
        Query                   $query,
        AttributeMetadataReader $attributeMetadataReader,
        Hydrator                $hydrator,
        private EntityPersister $entityPersister,
    ) {}

    /**
     * @param class-string $entityClass
     */
    public function find(string $entityClass, mixed $id, string|null $connection = null) : object|null
    {
        return $this->entityPersister->find(entityClass: $entityClass, id: $id, connectionName: $connection);
    }

    /**
     * @param class-string $entityClass
     * @param array<string, mixed> $criteria
     *
     * @return list<object>
     */
    public function findBy(
        string $entityClass,
        array  $criteria,
        string|null $orderBy = null,
        string|null $direction = null,
        ?int $limit = null,
        ?int $offset = null,
        string|null $connection = null,
    ) : array
    {
        return $this->entityPersister->findBy(
            entityClass   : $entityClass,
            criteria      : $criteria,
            orderBy       : $orderBy,
            direction     : $direction,
            limit         : $limit,
            offset        : $offset,
            connectionName: $connection,
        );
    }

    /**
     * @throws Throwable
     */
    public function insert(object $entity, string|null $connection = null) : void
    {
        $this->entityPersister->insert(entity: $entity, connectionName: $connection);
    }

    /**
     * @throws Throwable
     */
    public function update(object $entity, string|null $connection = null) : void
    {
        $this->entityPersister->update(entity: $entity, connectionName: $connection);
    }

    /**
     * @throws Throwable
     */
    public function delete(object $entity, string|null $connection = null) : void
    {
        $this->entityPersister->delete(entity: $entity, connectionName: $connection);
    }

    /**
     * @throws Throwable
     */
    public function refresh(object $entity, string|null $connection = null) : object
    {
        return $this->entityPersister->refresh(entity: $entity, connectionName: $connection);
    }
}
