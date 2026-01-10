<?php

declare(strict_types=1);

namespace Avax\Repository;

use Avax\Database\QueryBuilder\Core\Builder\QueryBuilder;
use Exception;
use RuntimeException;

/**
 * Base Settings
 *
 * Abstract class providing database operations for entities, including find, save, delete, list, and advanced queries.
 */
abstract class Repository
{
    /**
     * Settings constructor.
     *
     * Stores a builder factory; each call uses a fresh builder instance.
     *
     * @param QueryBuilder $queryBuilder The query builder instance for database operations.
     */
    public function __construct(protected QueryBuilder $queryBuilder) {}

    /**
     * @throws \Exception
     */
    public function findById(int $id): object|null
    {
        return $this->findOneBy(conditions: ['id' => $id]);
    }

    /**
     * Find one entity by conditions.
     *
     * @param array<string, mixed> $conditions Conditions for filtering.
     *
     * @return object|null The found entity or null.
     * @throws \Exception
     * @throws \Exception
     */
    public function findOneBy(array $conditions): object|null
    {
        try {
            $query = $this->query();

            foreach ($conditions as $column => $value) {
                $query->where(column: $column, operator: '=', value: $value);
            }

            $result = $query->first();

            return $result ? $this->mapToEntity(data: $result) : null;
        } catch (Exception $exception) {
            $this->logError(
                message: 'Failed to find one entity by conditions.',
                context: [
                    'conditions' => $conditions,
                    'exception'  => $exception,
                ]
            );
            throw $exception;
        }
    }

    protected function query(): QueryBuilder
    {
        return $this->queryBuilder->newQuery()->table($this->getTableName());
    }

    /**
     * Get the table name for the entity.
     *
     * @return string The name of the table.
     */
    protected function getTableName(): string
    {
        $entityClass = $this->getEntityClass();

        if (! method_exists(object_or_class: $entityClass, method: 'getTableName')) {
            throw new RuntimeException(
                message: sprintf(
                    'Entity class %s must implement a getTableName() method.',
                    $entityClass
                )
            );
        }

        return $entityClass::getTableName();
    }

    /**
     * Get the entity class for the repository.
     *
     * @return string The fully qualified class name of the entity.
     */
    abstract protected function getEntityClass(): string;

    // ===== CRUD Methods ===== //

    /**
     * Map a database row to an entity object.
     *
     * @param array<string, mixed> $data The database row data.
     *
     * @return object The mapped entity.
     */
    abstract protected function mapToEntity(array $data): object;

    protected function logError(string $message, array $context = []): void
    {
        logger(message: $message, context: $context, level: 'error');
    }

    /**
     * @throws \Exception
     */
    public function findAll(int|null $limit = null, int $offset = 0): array
    {
        $limit ??= 100;

        return $this->findBy(conditions: [], limit: $limit, offset: $offset);
    }

    /**
     * Find entities by conditions with optional pagination and sorting.
     *
     * @param array<string, mixed> $conditions Conditions for filtering.
     * @param string|null          $orderBy    Column to order by.
     * @param string|null          $direction  Sorting direction (ASC|DESC).
     * @param int|null             $limit      Max results to return.
     * @param int|null             $offset     Offset for pagination.
     *
     * @return array<object> The found entities.
     * @throws \Exception
     * @throws \Exception
     */
    public function findBy(
        array       $conditions,
        string|null $orderBy = null,
        string|null $direction = null,
        int|null    $limit = null,
        int|null    $offset = null
    ): array {
        try {
            $query = $this->query();

            foreach ($conditions as $column => $value) {
                $query->where(column: $column, operator: '=', value: $value);
            }

            if ($orderBy !== null && $orderBy !== '' && $orderBy !== '0') {
                $query->orderBy(column: $orderBy, direction: $direction ?? 'ASC');
            }

            if ($limit !== null) {
                $query->limit(limit: $limit);
            }

            if ($offset !== null) {
                $query->offset(offset: $offset);
            }

            $results = $query->get();

            return array_map(callback: [$this, 'mapToEntity'], array: $results);
        } catch (Exception $exception) {
            $this->logError(message: 'Failed to find entities by conditions.', context: [
                'conditions' => $conditions,
                'orderBy'    => $orderBy,
                'direction'  => $direction,
                'limit'      => $limit,
                'offset'     => $offset,
                'exception'  => $exception,
            ]);
            throw $exception;
        }
    }

    // ===== Generalized Query Methods ===== //

    /**
     * @throws \Exception
     */
    public function save(object $entity): void
    {
        $this->beforeSave(entity: $entity);

        $data = $this->mapToDatabase(entity: $entity);

        if (method_exists(object_or_class: $entity, method: 'getId') && $entity->getId() !== null) {
            $this->query()
                ->where(column: 'id', operator: '=', value: $entity->getId())
                ->update($data);
        } else {
            $id = $this->query()->insertGetId($data);

            if (method_exists(object_or_class: $entity, method: 'setId')) {
                $entity->setId($id);
            }
        }

        $this->afterSave(entity: $entity);
    }

    protected function beforeSave(object $entity): void
    {
        // Placeholder for pre-save logic.
    }

    /**
     * Map an entity object to a database row.
     *
     * @param object $entity The entity to map.
     *
     * @return array<string, mixed> The database row representation.
     */
    abstract protected function mapToDatabase(object $entity): array;

    protected function afterSave(object $entity): void
    {
        // Placeholder for post-save logic.
    }

    // ===== Hooks ===== //

    /**
     * @throws \Exception
     */
    public function delete(object $entity): void
    {
        if (! method_exists(object_or_class: $entity, method: 'getId') || $entity->getId() === null) {
            throw new RuntimeException(message: "Entity must have an ID to be deleted.");
        }

        $this->query()
            ->where(column: 'id', operator: '=', value: $entity->getId())
            ->delete();
    }

    /**
     * Check if an entity exists by conditions.
     *
     * @param array<string, mixed> $conditions Conditions for filtering.
     *
     * @return bool True if the entity exists, false otherwise.
     * @throws \Exception
     * @throws \Exception
     */
    public function exists(array $conditions): bool
    {
        try {
            $query = $this->query();

            foreach ($conditions as $column => $value) {
                $query->where(column: $column, operator: '=', value: $value);
            }

            return $query->exists();
        } catch (Exception $exception) {
            $this->logError(message: 'Failed to check if entity exists.', context: [
                'conditions' => $conditions,
                'exception'  => $exception,
            ]);
            throw $exception;
        }
    }

    // ===== Error Logging ===== //

    /**
     * Count entities by conditions.
     *
     * @param array<string, mixed> $conditions Conditions for filtering.
     *
     * @return int The number of matching entities.
     * @throws \Exception
     * @throws \Exception
     */
    public function count(array $conditions): int
    {
        try {
            $query = $this->query();

            foreach ($conditions as $column => $value) {
                $query->where(column: $column, operator: '=', value: $value);
            }

            return $query->count(column: '*');
        } catch (Exception $exception) {
            $this->logError(message: 'Failed to count entities.', context: [
                'conditions' => $conditions,
                'exception'  => $exception,
            ]);
            throw $exception;
        }
    }
}
