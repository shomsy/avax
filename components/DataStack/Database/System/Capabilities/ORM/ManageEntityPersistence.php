<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\ORM;

use Avax\Components\DataStack\Database\System\Capabilities\ORM\Persisters\EntityPersister;

final readonly class ManageEntityPersistence
{
    public function __construct(
        private EntityPersister $persister,
    ) {
    }

    public function persist(object $entity, string|null $connectionName = null): void
    {
        $this->persister->insert(entity: $entity, connectionName: $connectionName);
    }

    public function update(object $entity, string|null $connectionName = null): void
    {
        $this->persister->update(entity: $entity, connectionName: $connectionName);
    }

    public function remove(object $entity, string|null $connectionName = null): void
    {
        $this->persister->delete(entity: $entity, connectionName: $connectionName);
    }

    public function refresh(object $entity, string|null $connectionName = null): object
    {
        return $this->persister->refresh(entity: $entity, connectionName: $connectionName);
    }

    public function find(string $entityClass, mixed $id, string|null $connectionName = null): ?object
    {
        return $this->persister->find(entityClass: $entityClass, id: $id, connectionName: $connectionName);
    }

    public function findAll(string $entityClass, string|null $connectionName = null): array
    {
        return $this->persister->findAll(entityClass: $entityClass, connectionName: $connectionName);
    }
}
