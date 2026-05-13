<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\PublicSurface;

use Avax\Components\DataStack\Database\System\Capabilities\ORM\EntityManager as OrmEntityManager;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Hydration\Hydrator;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\IdentityMap\IdentityMap;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Metadata\AttributeMetadataReader;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Persisters\EntityPersister;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Query;

/**
 * EntityManager public surface — the user-facing entry point for ORM operations.
 *
 * Delegates to the canonical EntityPersister for persistence with lifecycle events.
 * This class exists so users can construct an EntityManager without knowing about
 * internal persistence details.
 */
final class EntityManager
{
    private OrmEntityManager $orm;

    public function __construct(Query $query, AttributeMetadataReader|null $metadataReader = null)
    {
        $metadataReader ??= new AttributeMetadataReader();
        $identityMap = new IdentityMap();
        $hydrator = new Hydrator($identityMap);
        $persister = new EntityPersister(
            query: $query,
            attributeMetadataReader: $metadataReader,
            hydrator: $hydrator,
        );
        $this->orm = new OrmEntityManager($persister);
    }

    /**
     * Persist a new entity.
     */
    public function persist(object $entity, string|null $connectionName = null): void
    {
        $this->orm->persist(entity: $entity, connectionName: $connectionName);
    }

    /**
     * Update an existing entity.
     */
    public function update(object $entity, string|null $connectionName = null): void
    {
        $this->orm->update(entity: $entity, connectionName: $connectionName);
    }

    /**
     * Remove an entity.
     */
    public function remove(object $entity, string|null $connectionName = null): void
    {
        $this->orm->remove(entity: $entity, connectionName: $connectionName);
    }

    /**
     * Find an entity by its identifier.
     *
     * @param  class-string  $entityClass
     */
    public function find(string $entityClass, mixed $id, string|null $connectionName = null): ?object
    {
        return $this->orm->find(entityClass: $entityClass, id: $id, connectionName: $connectionName);
    }
}
