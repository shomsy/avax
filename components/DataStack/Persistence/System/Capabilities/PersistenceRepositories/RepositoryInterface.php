<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\PersistenceRepositories;

/**
 * Repository contract for entity persistence operations.
 *
 * Repositories provide a collection-like interface for accessing
 * domain entities. They abstract the underlying storage mechanism
 * and work with the UnitOfWork for write operations.
 *
 * @template T of object
 */
interface RepositoryInterface
{
    /**
     * Find an entity by its primary identifier.
     *
     * @return T|null
     */
    public function findById(string|int $id) : object|null;

    /**
     * Find all entities, optionally with pagination.
     *
     * @return array<T>
     */
    public function findAll(int|null $limit = null, int $offset = 0) : array;

    /**
     * Find entities matching the given criteria.
     *
     * @param  array<string, mixed>  $criteria
     * @param  array<string, string>|null  $orderBy  Column => direction (ASC/DESC)
     * @return array<T>
     */
    public function findBy(
        array $criteria, array|null $orderBy = null, int|null $limit = null, int|null $offset = null,
    ): array;

    /**
     * Find a single entity matching the given criteria.
     *
     * @param  array<string, mixed>  $criteria
     * @return T|null
     */
    public function findOneBy(array $criteria) : object|null;

    /**
     * Persist an entity (schedule for insert or update via UnitOfWork).
     *
     * @param  T  $entity
     */
    public function save(object $entity): void;

    /**
     * Remove an entity (schedule for deletion via UnitOfWork).
     *
     * @param  T  $entity
     */
    public function delete(object $entity): void;

    /**
     * Check if an entity exists matching the given criteria.
     *
     * @param  array<string, mixed>  $criteria
     */
    public function exists(array $criteria): bool;

    /**
     * Count entities matching the given criteria.
     *
     * @param  array<string, mixed>  $criteria
     */
    public function count(array $criteria = []): int;
}
