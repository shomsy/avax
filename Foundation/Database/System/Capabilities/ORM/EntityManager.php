<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\ORM;

use Avax\Database\System\Capabilities\ORM\Hydration\Hydrator;
use Avax\Database\System\Capabilities\ORM\Metadata\AttributeMetadataReader;
use Avax\Database\System\Capabilities\ORM\Persisters\EntityPersister;
use Avax\Database\System\Capabilities\Query\Query;

final readonly class EntityManager
{
    public function __construct(
        private Query                   $query,
        private AttributeMetadataReader $metadataReader,
        private Hydrator                $hydrator,
        private EntityPersister         $persister
    ) {}

    /**
     * @param class-string $entityClass
     */
    public function find(string $entityClass, mixed $id, string|null $connection = null) : ?object
    {
        return $this->persister->find(entityClass: $entityClass, id: $id, connectionName: $connection);
    }

    /**
     * @param class-string         $entityClass
     * @param array<string, mixed> $criteria
     *
     * @return list<object>
     */
    public function findBy(
        string  $entityClass,
        array   $criteria,
        ?string $orderBy = null,
        ?string $direction = null,
        ?int    $limit = null,
        ?int    $offset = null,
        ?string $connection = null
    ) : array
    {
        return $this->persister->findBy(
            entityClass   : $entityClass,
            criteria      : $criteria,
            orderBy       : $orderBy,
            direction     : $direction,
            limit         : $limit,
            offset        : $offset,
            connectionName: $connection
        );
    }

    public function insert(object $entity, ?string $connection = null) : void
    {
        $this->persister->insert(entity: $entity, connectionName: $connection);
    }

    public function update(object $entity, ?string $connection = null) : void
    {
        $this->persister->update(entity: $entity, connectionName: $connection);
    }

    public function delete(object $entity, ?string $connection = null) : void
    {
        $this->persister->delete(entity: $entity, connectionName: $connection);
    }

    public function refresh(object $entity, ?string $connection = null) : object
    {
        return $this->persister->refresh(entity: $entity, connectionName: $connection);
    }
}
