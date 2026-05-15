<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\PublicSurface;

use Avax\Components\DataStack\Database\System\Capabilities\ORM\Hydration\Hydrator;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\IdentityMap\IdentityMap;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\ManageEntityPersistence as OrmEntityManager;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Metadata\AttributeMetadataReader;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Persisters\EntityPersister;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Query;

final class ManageEntityPersistence
{
    private OrmEntityManager $orm;

    public function __construct(Query $query, AttributeMetadataReader $metadataReader)
    {
        $identityMap = new IdentityMap();
        $hydrator = new Hydrator($identityMap);
        $persister = new EntityPersister(
            query: $query,
            attributeMetadataReader: $metadataReader,
            hydrator: $hydrator,
        );
        $this->orm = new OrmEntityManager($persister);
    }

    public function persist(object $entity, string|null $connectionName = null): void
    {
        $this->orm->persist(entity: $entity, connectionName: $connectionName);
    }

    public function update(object $entity, string|null $connectionName = null): void
    {
        $this->orm->update(entity: $entity, connectionName: $connectionName);
    }

    public function remove(object $entity, string|null $connectionName = null): void
    {
        $this->orm->remove(entity: $entity, connectionName: $connectionName);
    }

    public function find(string $entityClass, mixed $id, string|null $connectionName = null): ?object
    {
        return $this->orm->find(entityClass: $entityClass, id: $id, connectionName: $connectionName);
    }
}
