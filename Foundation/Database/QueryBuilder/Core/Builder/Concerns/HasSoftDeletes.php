<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Core\Builder\Concerns;

use Avax\Database\QueryBuilder\Core\Builder\QueryBuilder;
use Throwable;

/**
 * Trait HasSoftDeletes
 *
 * -- intent: extend the query builder with automated soft-deletion filtering and management.
 */
trait HasSoftDeletes
{
    /**
     * Whether soft deletion filters should be bypassed.
     */
    protected bool $withTrashed = false;

    /**
     * Filter for specifically retrieving deleted records.
     */
    protected bool $onlyTrashed = false;

    /**
     * Include soft-deleted records in the query results.
     *
     * -- intent: disable the automatic filter for the 'deleted_at' column.
     *
     * @return QueryBuilder|HasSoftDeletes
     */
    public function withTrashed() : self
    {
        $this->withTrashed = true;

        return $this;
    }

    /**
     * Filter results to only include soft-deleted records.
     *
     * -- intent: pivot the logical filter to focus solely on historically removed data.
     *
     * @return QueryBuilder|HasSoftDeletes
     */
    public function onlyTrashed() : self
    {
        $this->onlyTrashed = true;

        return $this;
    }

    /**
     * Restore all soft-deleted records matching the current criteria.
     *
     * -- intent: revert the deletion state by nullifying the 'deleted_at' column.
     *
     * @param string $column The technical deletion indicator column
     *
     * @return bool
     * @throws Throwable If SQL execution fails
     */
    public function restore(string $column = 'deleted_at') : bool
    {
        return $this->update(values: [$column => null]);
    }

    /**
     * Internal technician to apply the appropriate soft-deletion SQL filters.
     *
     * -- intent: automate data visibility constraints based on feature state.
     *
     * @param string $column Technical name of the deletion timestamp
     *
     * @return void
     */
    protected function applySoftDeleteFilter(string $column = 'deleted_at') : void
    {
        if ($this->withTrashed) {
            return;
        }

        if ($this->onlyTrashed) {
            $this->whereNotNull(column: $column);

            return;
        }

        $this->whereNull(column: $column);
    }

    /**
     * Add a WHERE NOT NULL filter to the query.
     *
     * -- intent: ensure that a specific column contains active data.
     *
     * @param string $column  Technical column identifier
     * @param string $boolean Logical joiner
     *
     * @return QueryBuilder|HasSoftDeletes
     */
    public function whereNotNull(string $column, string $boolean = 'AND') : self
    {
        return $this->whereNull(column: $column, boolean: $boolean, not: true);
    }

    /**
     * Add a WHERE NULL filter to the query.
     *
     * -- intent: check for the absence of data in a specific column.
     *
     * @param string      $column  Technical column identifier
     * @param string|null $boolean Logical joiner
     * @param bool        $not     Whether to use IS NOT NULL
     *
     * @return QueryBuilder|HasSoftDeletes
     */
    public function whereNull(string $column, string|null $boolean = null, bool $not = false) : self
    {
        $boolean  ??= 'AND';
        $operator = $not ? 'IS NOT NULL' : 'IS NULL';

        $this->state->wheres[] = [
            'type'     => 'Null',
            'column'   => $column,
            'operator' => $operator,
            'boolean'  => $boolean
        ];

        return $this;
    }
}
