<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\Repositories;

use Avax\Components\DataStack\Persistence\System\Capabilities\UnitOfWork\UnitOfWorkInterface;

/**
 * Base repository implementation that delegates reads to a storage backend
 * and writes to the UnitOfWork.
 *
 * Concrete repositories should extend this class and define the entity class.
 *
 * @template T of object
 *
 * @implements RepositoryInterface<T>
 */
abstract class Repository implements RepositoryInterface
{
    public function __construct(
        private readonly RepositoryStorageInterface $repositoryStorage,
        private readonly UnitOfWorkInterface $unitOfWork,
    ) {
    }

    public function findById(string|int $id) : object|null
    {
        return $this->repositoryStorage->find(
            entityClass: $this->entityClass(),
            id         : $id,
        );
    }

    /**
     * @return class-string<T>
     */
    abstract protected function entityClass(): string;

    public function findAll(int|null $limit = null, int $offset = 0) : array
    {
        return $this->findBy(criteria: [], limit: $limit ?? 100, offset: $offset);
    }

    public function findBy(
        array $criteria, array|null $orderBy = null, int|null $limit = null, int|null $offset = null,
    ): array {
        return $this->repositoryStorage->findBy(
            entityClass: $this->entityClass(),
            criteria   : $criteria,
            orderBy    : $orderBy,
            limit      : $limit,
            offset     : $offset,
        );
    }

    public function findOneBy(array $criteria) : object|null
    {
        $results = $this->findBy(criteria: $criteria, limit: 1);

        return $results[0] ?? null;
    }

    public function save(object $entity): void
    {
        $this->unitOfWork->persist($entity);
    }

    public function delete(object $entity): void
    {
        $this->unitOfWork->remove($entity);
    }

    public function exists(array $criteria): bool
    {
        return $this->repositoryStorage->exists(
            entityClass: $this->entityClass(),
            criteria   : $criteria,
        );
    }

    public function count(array $criteria = []): int
    {
        return $this->repositoryStorage->count(
            entityClass: $this->entityClass(),
            criteria   : $criteria,
        );
    }
}
