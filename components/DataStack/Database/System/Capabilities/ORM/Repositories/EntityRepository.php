<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\ORM\Repositories;

use Avax\Components\DataStack\Database\System\Capabilities\ORM\EntityManager;
use Throwable;

/**
 * Doctrine-style generic repository bound to one entity class.
 *
 * @template TEntity of object
 */
class EntityRepository
{
    /**
     * @param class-string<TEntity> $entityClass
     */
    public function __construct(
        protected readonly EntityManager $entityManager,
        protected readonly string $entityClass,
    ) {}

    /**
     * @return TEntity|null
     * @throws Throwable
     */
    public function find(mixed $id, string $connectionName = null) : object|null
    {
        return $this->entityManager->find(
            entityClass   : $this->entityClass,
            id            : $id,
            connectionName: $connectionName,
        );
    }

    /**
     * @param array<string, mixed> $criteria
     *
     * @return TEntity|null
     * @throws Throwable
     */
    public function findOneBy(array $criteria, string $connectionName = null) : object|null
    {
        return $this->findBy(criteria: $criteria, limit: 1, connectionName: $connectionName)[0] ?? null;
    }

    /**
     * @param array<string, mixed> $criteria
     *
     * @return list<TEntity>
     * @throws Throwable
     */
    public function findBy(
        array  $criteria,
        string $orderBy = null,
        string $direction = null,
        int    $limit = null,
        int    $offset = null,
        string $connectionName = null,
    ) : array
    {
        return $this->entityManager->findBy(
            entityClass   : $this->entityClass,
            criteria      : $criteria,
            orderBy       : $orderBy,
            direction     : $direction,
            limit         : $limit,
            offset        : $offset,
            connectionName: $connectionName,
        );
    }

    /**
     * @return list<TEntity>
     * @throws Throwable
     */
    public function findAll(string $connectionName = null) : array
    {
        return $this->entityManager->findBy(
            entityClass   : $this->entityClass,
            criteria      : [],
            connectionName: $connectionName,
        );
    }
}
