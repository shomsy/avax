<?php

declare(strict_types=1);

namespace Avax\Database;

use Avax\Database\System\Capabilities\ORM\EntityManager as EntityManagerCapability;
use Avax\Database\System\Capabilities\ORM\Metadata\EntityMetadata;
use Avax\Database\System\Capabilities\ORM\Repositories\EntityRepository;
use Throwable;

final readonly class EntityManager
{
    public function __construct(private EntityManagerCapability $entityManager) {}

    /**
     * @param class-string $entityClass
     */
    public function metadata(string $entityClass) : EntityMetadata
    {
        return $this->entityManager->metadata(entityClass: $entityClass);
    }

    /**
     * @param class-string $entityClass
     *
     * @throws Throwable
     */
    public function find(string $entityClass, mixed $id, ?string $connectionName = null) : ?object
    {
        return $this->entityManager->find(
            entityClass   : $entityClass,
            id            : $id,
            connectionName: $connectionName
        );
    }

    public function persist(object $entity) : void
    {
        $this->entityManager->persist(entity: $entity);
    }

    public function remove(object $entity) : void
    {
        $this->entityManager->remove(entity: $entity);
    }

    /**
     * @throws Throwable
     */
    public function flush(?string $connectionName = null) : void
    {
        $this->entityManager->flush(connectionName: $connectionName);
    }

    public function clear() : void
    {
        $this->entityManager->clear();
    }

    /**
     * @throws Throwable
     */
    public function refresh(object $entity, ?string $connectionName = null) : object
    {
        return $this->entityManager->refresh(entity: $entity, connectionName: $connectionName);
    }

    /**
     * @param class-string $entityClass
     */
    public function repository(string $entityClass) : EntityRepository
    {
        return $this->entityManager->repository(entityClass: $entityClass);
    }

    /**
     * @throws Throwable
     */
    public function transactional(callable $callback, ?string $connectionName = null) : mixed
    {
        return $this->entityManager->transactional(callback: $callback, connectionName: $connectionName);
    }
}
