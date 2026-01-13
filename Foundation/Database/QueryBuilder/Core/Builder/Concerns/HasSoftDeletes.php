<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Core\Builder\Concerns;

use Avax\Database\Query\AST\WhereNode;
use Throwable;

/**
 * Trait providing automated soft-deletion filtering and data lifecycle management.
 *
 * -- intent:
 * Extends the QueryBuilder with transparent handling of "shadow-deleted"
 * records—data that is logically removed from the application but physically
 * persists in the database with a deletion timestamp.
 *
 * -- invariants:
 * - By default, all queries must exclude records with a non-null deletion timestamp.
 * - The soft-delete filter must be injectable at the final compilation stage.
 * - Every mode modification (with/only trashed) must return a new, cloned builder instance.
 *
 * -- boundaries:
 * - Does NOT handle the physical schema definitions (delegated to Schema/Migrations).
 *
 * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/SoftDeletes.md
 * - Only applies to tables containing a technical deletion column (default 'deleted_at').
 */
trait HasSoftDeletes
{
    /** @var bool Flag indicating if deleted records should be included in the resulting dataset */
    protected bool $withTrashed = false;

    /** @var bool Flag indicating if the query should exclusively retrieve deleted records */
    protected bool $onlyTrashed = false;

    /**
     * Include soft-deleted records in the resulting query dataset.
     *
     * -- intent:
     * Explicitly override the default exclusion policy, allowing for auditing
     * or complex reporting across both active and deleted
     *
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/SoftDeletes.md#including-deleted-records
     *      domain records.
     *
     * @return \Avax\Database\QueryBuilder\Core\Builder\QueryBuilder|\Avax\Database\QueryBuilder\Core\Builder\Concerns\HasSoftDeletes A
     *                                                                                                                                fresh,
     *                                                                                                                                cloned
     *                                                                                                                                builder
     *                                                                                                                                instance
     *                                                                                                                                with
     *                                                                                                                                soft-deleted
     *                                                                                                                                inclusion
     *                                                                                                                                active.
     */
    public function withTrashed() : self
    {
        $clone              = clone $this;
        $clone->withTrashed = true;

        return $clone;
    }

    /**
     * Filter the results     *
     *
     * @return \Avax\Database\QueryBuilder\Core\Builder\Concerns\HasSoftDeletes|\Avax\Database\QueryBuilder\Core\Builder\QueryBuilder \Avax\Database\QueryBuilder\Core\Builder\QueryBuilder|\Avax\Database\QueryBuilder\Core\Builder\Concerns\HasSoftDeletes
     *                                                                                                                                \Avax\Database\QueryBuilder\Core\Builder\QueryBuilder|\Avax\Database\QueryBuilder\Core\Builder\Concerns\HasSoftDeletes
     *                                                                                                                                A
     *                                                                                                                                fresh,
     *                                                                                                                                cloned
     *                                                                                                                                builder
     *                                                                                                                                instance
     *                                                                                                                                targeting
     *                                                                                                                                only
     *                                                                                                                                deleted
     *                                                                                                                                records.
     *
     * @see
     * https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/SoftDeletes.md#only-deleted-records
     * to exclusively include records marked as soft-deleted.
     *
     * -- intent:
     * Isolate domain records that have been logically removed from the active
     * set, typically for recovery, permanent destruction, or auditing.
     */
    public function onlyTrashed() : self
    {
        $clone              = clone $this;
        $clone->onlyTrashed = true;

        return $clone;
    }

    /**
     * Restore domain records from their logically deleted state.
     *
     * -- intent:
     * Nullify the deletion timestamp for records matching the current criteria,
     * bringing them back into the active result se
     *
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/SoftDeletes.md#restoring-records
     * t.
     *
     * @param string $column The technical deletion field identifier (defaults to 'deleted_at').
     *
     * @return bool True if the records were successfully marked as active.
     *
     * @throws Throwable If the restoration update fails at the persistence layer.
     */
    public function restore(string $column = 'deleted_at') : bool
    {
        return $this->update(values: [$column => null]);
    }

    /**
     * Internal technician for injecting the relevant soft-delete filters.
     *
     * -- intent:
     * Provide an automated mechanism for enforcing data isolation based on
     * the current feature flags (withTrashed, onlyTrashed), ensuring that
     * logical deletion is respected in all final SQL instructions.
     *
     * @param string $column The technical deletion field identifier (defaults to 'deleted_at').
     *
     * @return \Avax\Database\QueryBuilder\Core\Builder\QueryBuilder|\Avax\Database\QueryBuilder\Core\Builder\Concerns\HasSoftDeletes A
     *                                                                                                                                fresh,
     *                                                                                                                                cloned
     *                                                                                                                                builder
     *                                                                                                                                instance
     *                                                                                                                                with
     *                                                                                                                                the
     *                                                                                                                                appropriate
     *                                                                                                                                deletion
     *                                                                                                                                filters
     *                                                                                                                                injected.
     */
    public function withSoftDeleteFilter(string $column = 'deleted_at') : self
    {
        if ($this->withTrashed) {
            return $this;
        }

        if ($this->onlyTrashed) {
            return $this->whereNotNull(column: $column);
        }

        return $this->whereNull(column: $column);
    }

    /**
     * Add a filtering criterion to check for the presence of a value (IS NOT NULL).
     *
     * -- intent:
     * Provide an expressive DSL for SQL "IS NOT NULL" logic, acting as a
     * categorical filter for required technical metadata.
     *
     * @param string $column  The technical field name to target for the non-null check.
     * @param string $boolean The logical joiner used to attach this condition ('AND' or 'OR').
     *
     * @return \Avax\Database\QueryBuilder\Core\Builder\QueryBuilder|\Avax\Database\QueryBuilder\Core\Builder\Concerns\HasSoftDeletes A
     *                                                                                                                                fresh,
     *                                                                                                                                cloned
     *                                                                                                                                builder
     *                                                                                                                                instance
     *                                                                                                                                with
     *                                                                                                                                the
     *                                                                                                                                non-null
     *                                                                                                                                filter.
     */
    public function whereNotNull(string $column, string $boolean = 'AND') : self
    {
        return $this->whereNull(column: $column, boolean: $boolean, not: true);
    }

    /**
     * Add a filtering criterion to check for the absence of a value (IS NULL).
     *
     * -- intent:
     * Provide an expressive DSL for SQL "IS NULL" logic, primarily used for
     * checking existence flags or soft-delete statuses.
     *
     * @param string      $column  The technical field name to target for the null check.
     * @param string|null $boolean The logical joiner used to attach this condition ('AND' or 'OR').
     * @param bool        $not     Flag indicating whether to check for existence (IS NOT NULL) instead.
     *
     * @return \Avax\Database\QueryBuilder\Core\Builder\QueryBuilder|\Avax\Database\QueryBuilder\Core\Builder\Concerns\HasSoftDeletes A
     *                                                                                                                                fresh,
     *                                                                                                                                cloned
     *                                                                                                                                builder
     *                                                                                                                                instance
     *                                                                                                                                with
     *                                                                                                                                the
     *                                                                                                                                null
     *                                                                                                                                filter.
     */
    public function whereNull(string $column, string|null $boolean = null, bool $not = false) : self
    {
        $boolean  ??= 'AND';
        $operator = $not ? 'IS NOT NULL' : 'IS NULL';

        $clone        = clone $this;
        $clone->state = $clone->state->addWhere(where: new WhereNode(
            column  : $column,
            operator: $operator,
            type    : 'Null',
            boolean : $boolean
        ));

        return $clone;
    }
}
