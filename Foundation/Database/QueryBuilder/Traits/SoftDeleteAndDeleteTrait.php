<?php
/** @noinspection SqlWithoutWhere */

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Traits;

use DateTime;
use Avax\Database\QueryBuilder\Enums\QueryBuilderEnum;

/**
 * Trait SoftDeleteAndDeleteTrait
 *
 * Provides functionality for managing soft and permanent deletes, including:
 * - Soft delete support with timestamp tracking.
 * - Restore functionality for soft-deleted records.
 * - Permanent delete operations.
 * - Cascading delete operations across related tables.
 * - DELETE JOIN operations for multi-table deletions.
 * - Table truncation support.
 *
 * Implements the **Unit of Work** pattern to defer execution of delete-related operations
 * until explicitly committed.
 */
trait SoftDeleteAndDeleteTrait
{
    /**
     * Indicates whether soft delete functionality is enabled.
     */
    private bool $softDeletes = false;

    /**
     * Indicates whether to include soft-deleted records in queries.
     */
    private bool $withTrashed = false;

    /**
     * Indicates whether to retrieve only soft-deleted records.
     */
    private bool $onlyTrashed = false;

    /**
     * The name of the column used for soft deletes.
     */
    private string $deletedColumn = 'deleted_at';

    /**
     * Enables soft deletes and optionally sets the column used for soft deletion timestamps.
     */
    public function enableSoftDeletes(bool $softDeletes, string|null $deletedColumn = null) : static
    {
        $this->softDeletes = $softDeletes;
        if ($deletedColumn !== null) {
            $this->deletedColumn = $deletedColumn;
        }

        return $this;
    }

    /**
     * Includes soft-deleted records in queries.
     */
    public function withTrashed() : static
    {
        $this->withTrashed = true;
        $this->onlyTrashed = false;

        return $this;
    }

    /**
     * Restricts queries to only soft-deleted records.
     */
    public function onlyTrashed() : static
    {
        $this->onlyTrashed = true;
        $this->withTrashed = false;

        return $this;
    }

    /**
     * Resets filters applied for soft delete queries.
     */
    public function resetSoftDeleteFilters() : static
    {
        $this->withTrashed = false;
        $this->onlyTrashed = false;

        return $this;
    }

    /**
     * Applies soft delete conditions to queries.
     */
    public function applySoftDeleteConditions() : string
    {
        if (! $this->softDeletes) {
            return '';
        }

        return match (true) {
            $this->onlyTrashed   => sprintf(' AND %s IS NOT NULL', $this->deletedColumn),
            ! $this->withTrashed => sprintf(' AND %s IS NULL', $this->deletedColumn),
            default              => '',
        };
    }

    /**
     * Marks records as soft deleted by setting the deleted timestamp.
     *
     * @throws \Avax\Database\QueryBuilder\Exception\QueryBuilderException
     */
    public function softDelete() : static
    {
        return $this->registerForUnitOfWork(
            sql       : 'UPDATE ' . $this->getTableName() .
                        ' SET ' . $this->deletedColumn . ' = :deleted_at ' . $this->buildWhereClauses(),
            parameters: [':deleted_at' => (new DateTime())->format('Y-m-d H:i:s')],
            operation : QueryBuilderEnum::QUERY_TYPE_SOFT_DELETE
        );
    }

    /**
     * Registers an operation for deferred execution using the Unit of Work pattern.
     *
     * @throws \Avax\Database\QueryBuilder\Exception\QueryBuilderException
     * @throws \Avax\Database\QueryBuilder\Exception\QueryBuilderException
     */
    private function registerForUnitOfWork(string $sql, array $parameters, QueryBuilderEnum $operation) : static
    {
        $pdo       = $this->getConnection();
        $statement = $pdo->prepare($sql);

        $this
            ->getUnitOfWork()
            ->registerQuery(
                operation : $operation,
                statement : $statement,
                parameters: $parameters
            );

        return $this;
    }


    /**
     * Restores soft-deleted records by setting the deleted timestamp to NULL.
     *
     * @throws \Avax\Database\QueryBuilder\Exception\QueryBuilderException
     */
    public function restore() : static
    {
        return $this->registerForUnitOfWork(
            sql       : 'UPDATE ' . $this->getTableName() .
                        ' SET ' . $this->deletedColumn . ' = NULL ' . $this->buildWhereClauses(),
            parameters: [],
            operation : QueryBuilderEnum::QUERY_TYPE_RESTORE
        );
    }

    /**
     * Permanently deletes records without applying soft deletes.
     *
     * @throws \Avax\Database\QueryBuilder\Exception\QueryBuilderException
     */
    public function forceDelete() : static
    {
        return $this->delete();
    }

    /**
     * Registers a delete operation in the Unit of Work queue.
     *
     * @throws \Avax\Database\QueryBuilder\Exception\QueryBuilderException
     */
    public function delete() : static
    {
        return $this->registerForUnitOfWork(
            sql       : 'DELETE FROM ' . $this->getTableName() . ' ' . $this->buildWhereClauses(),
            parameters: [],
            operation : QueryBuilderEnum::QUERY_TYPE_DELETE
        );
    }

    /**
     * Registers a cascading delete operation for related tables.
     *
     * @throws \Avax\Database\QueryBuilder\Exception\QueryBuilderException
     */
    public function cascadeDelete(array $relatedTables) : static
    {
        foreach ($relatedTables as $table) {
            $this->registerForUnitOfWork(
                sql       : 'DELETE FROM ' . $table . ' ' . $this->buildWhereClauses(),
                parameters: [],
                operation : QueryBuilderEnum::QUERY_TYPE_CASCADE_DELETE
            );
        }

        return $this->delete();
    }

    /**
     * Registers a DELETE JOIN operation in Unit of Work.
     *
     * @throws \Avax\Database\QueryBuilder\Exception\QueryBuilderException
     */
    public function deleteJoin(string $joinTable, string $joinCondition) : static
    {
        return $this->registerForUnitOfWork(
            sql       : sprintf(
                            'DELETE %s FROM %s INNER JOIN %s ON %s %s',
                            $this->getTableName(),
                            $this->getTableName(),
                            $joinTable,
                            $joinCondition,
                            $this->buildWhereClauses()
                        ),
            parameters: [],
            operation : QueryBuilderEnum::QUERY_TYPE_DELETE_JOIN
        );
    }

    /**
     * Registers a truncate operation in Unit of Work.
     *
     * @throws \Avax\Database\QueryBuilder\Exception\QueryBuilderException
     */
    public function truncate() : static
    {
        return $this->registerForUnitOfWork(
            sql       : 'TRUNCATE TABLE ' . $this->getTableName(),
            parameters: [],
            operation : QueryBuilderEnum::QUERY_TYPE_TRUNCATE
        );
    }
}
