<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\ORM\EntityRepositories;

use Avax\Components\DataStack\Database\System\Capabilities\ORM\ManageEntityPersistence;
use RuntimeException;
use Throwable;

/**
 * Doctrine-style generic repository bound to one entity class.
 *
 * @template TEntity of object
 */
class EntityRepository
{
    /**
     * @param  class-string<TEntity>  $entityClass
     */
    public function __construct(
        protected readonly ManageEntityPersistence $entityManager,
        protected readonly string $entityClass,
    ) {
    }

    /**
     * @return TEntity|null
     *
     * @throws Throwable
     */
    public function find(mixed $id, string|null $connectionName = null) : object|null
    {
        $entity = $this->entityManager->find(
            entityClass   : $this->entityClass,
            id            : $id,
            connectionName: $connectionName,
        );

        if ($entity === null) {
            return null;
        }

        if (! $entity instanceof $this->entityClass) {
            throw new RuntimeException(sprintf(
                                           'Entity manager returned [%s] for repository [%s].',
                                           $entity::class,
                                           $this->entityClass,
                                       ));
        }

        return $entity;
    }

    /**
     * @param  array<string, mixed>  $criteria
     * @return TEntity|null
     *
     * @throws Throwable
     */
    public function findOneBy(array $criteria, string|null $connectionName = null) : object|null
    {
        return $this->findBy(criteria: $criteria, limit: 1, connectionName: $connectionName)[0] ?? null;
    }

    /**
     * @param  array<string, mixed>  $criteria
     * @return list<TEntity>
     *
     * @throws Throwable
     */
    public function findBy(
        array $criteria, string|null $orderBy = null, string|null $direction = null, int|null $limit = null, int|null $offset = null, string|null $connectionName = null,
    ): array {
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
     *
     * @throws Throwable
     */
    public function findAll(string|null $connectionName = null) : array
    {
        return $this->entityManager->findBy(
            entityClass   : $this->entityClass,
            criteria      : [],
            connectionName: $connectionName,
        );
    }
}
