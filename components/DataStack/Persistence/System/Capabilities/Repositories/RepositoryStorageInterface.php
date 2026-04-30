<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\Repositories;

/**
 * Contract for the read-side storage backend used by Repositories.
 *
 * This abstracts the actual data fetching mechanism. Implementations
 * may use the Database component's QueryBuilder, raw PDO, or any
 * other data source.
 */
interface RepositoryStorageInterface
{
    /**
     * Find a single entity by its identifier.
     *
     * @param class-string $entityClass
     */
    public function find(string $entityClass, string|int $id) : object|null;

    /**
     * Find entities by criteria with optional ordering and pagination.
     *
     * @param class-string         $entityClass
     * @param array<string, mixed> $criteria
     * @param array<string, string>|null $orderBy
     *
     * @return array<object>
     */
    public function findBy(
        string $entityClass,
        array  $criteria,
        array  $orderBy = null,
        int    $limit = null,
        int    $offset = null,
    ) : array;

    /**
     * Check if any entity exists matching the criteria.
     *
     * @param class-string $entityClass
     * @param array<string, mixed> $criteria
     */
    public function exists(string $entityClass, array $criteria) : bool;

    /**
     * Count entities matching the criteria.
     *
     * @param class-string $entityClass
     * @param array<string, mixed> $criteria
     */
    public function count(string $entityClass, array $criteria) : int;
}
