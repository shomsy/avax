<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database;

use Avax\Components\DataStack\Database\System\Capabilities\ORM\EntityManager as EntityManagerCapability;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Metadata\EntityMetadata;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Repositories\EntityRepository;
use Throwable;

final readonly class EntityManager
{
    public function __construct(private EntityManagerCapability $entityManagerCapability) {}

    /**
     * @param class-string $entityClass
     */
    public function metadata(string $entityClass) : EntityMetadata
    {
        return $this->entityManagerCapability->metadata(entityClass: $entityClass);
    }

    /**
     * @param class-string $entityClass
     *
     * @throws Throwable
     */
    public function find(string $entityClass, mixed $id, ?string $connectionName = null) : object|null
    {
        return $this->entityManagerCapability->find(
            entityClass   : $entityClass,
            id            : $id,
            connectionName: $connectionName,
        );
    }

    public function persist(object $entity) : void
    {
        $this->entityManagerCapability->persist(entity: $entity);
    }

    public function remove(object $entity) : void
    {
        $this->entityManagerCapability->remove(entity: $entity);
    }

    /**
     * @throws Throwable
     */
    public function flush(?string $connectionName = null) : void
    {
        $this->entityManagerCapability->flush(connectionName: $connectionName);
    }

    public function clear() : void
    {
        $this->entityManagerCapability->clear();
    }

    /**
     * @throws Throwable
     */
    public function refresh(object $entity, ?string $connectionName = null) : object
    {
        return $this->entityManagerCapability->refresh(entity: $entity, connectionName: $connectionName);
    }

    /**
     * @param class-string $entityClass
     */
    public function repository(string $entityClass) : EntityRepository
    {
        return $this->entityManagerCapability->repository(entityClass: $entityClass);
    }

    /**
     * @throws Throwable
     */
    public function transactional(callable $callback, ?string $connectionName = null) : mixed
    {
        return $this->entityManagerCapability->transactional(callback: $callback, connectionName: $connectionName);
    }
}
