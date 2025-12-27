<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Core\Builder\Concerns;

use Throwable;

/**
 * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/Mutations.md
 * Trait providing complex data modification capabilities for the QueryBuilder.
 *
 * -- intent:
 * Extends the QueryBuilder with advanced, industrialized mutation patterns
 * such as atomic UPSERT (insert-or-update) and thread-safe arithmetic
 * operations (increment/decrement) at the database level.
 *
 * -- invariants:
 * - Mutations must be dispatched via the QueryOrchestrator to ensure atomicity.
 * - Arithmetic operations must be performed using database-side expressions
 *   to prevent race conditions.
 * - UPSERT operations must normalize input values into a consistent batch format.
 *
 * -- boundaries:
 * - Does NOT handle SQL compilation (delegated to Grammar).
 * - Only applies to compatible database engines that support complex mutations.
 */
trait HasAdvancedMutations
{
    /**
     * Perform an atomic UPSERT (Insert or Update) operation.
     *
     * -- intent:
     * Dispatches a single instruction to the database to either insert new
     * records or update existing ones if a unique constraint conflict occurs,
     * significantly reducing net database round-trips.
     *
     * @param array        $values   A single associative array or a collection of arrays representing records.
     * @param array|string $uniqueBy The collection of technical field names that define the unique constraint.
     * @param array|null   $update   The collection of technical fields to modify upon conflict (defaults to all
     *                               provided values).
     *
     * @return int The total number of affected rows (database-specific semantics apply).
     * @throws Throwable If the SQL compilation for the specific dialect or physical execution fails.
     */
    public function upsert(array $values, array|string $uniqueBy, array|null $update = null) : int
    {
        if (empty($values)) {
            return 0;
        }

        if (! is_array(value: reset(array: $values))) {
            $values = [$values];
        }

        if ($update === null) {
            $update = array_keys(array: reset(array: $values));
        }

        $state = $this->state
            ->withValues(values: $values)
            ->withUpdateColumns(columns: (array) $update);

        $sql = $this->grammar->compileUpsert(
            state   : $state,
            uniqueBy: (array) $uniqueBy,
            update  : $state->updateColumns
        );

        return $this->orchestrator->execute(
            sql     : $sql,
            bindings: $state->getBindings()
        )->getAffectedRows();
    }

    /**
     * Atomically increment a numeric field by a specific quantity.
     *
     * -- intent:
     * Execute a server-side addition to a specific column, ensuring that
     * the operation is thread-safe and immune to typical application-level
     * read-modify-write race conditions.
     *
     * @param string         $column The technical name of the numeric field to increment.
     * @param int|float|null $amount The quantity to add (defaults to 1).
     * @param array          $extra  Optional additional fields to update simultaneously for auditing or state tracking.
     *
     * @return bool True if the operation was successful.
     * @throws Throwable If the resulting SQL update execution fails.
     */
    public function increment(string $column, int|float|null $amount = null, array $extra = []) : bool
    {
        $amount  ??= 1;
        $wrapped = $this->grammar->wrap(value: $column);
        $update  = array_merge([$column => $this->raw(value: "{$wrapped} + {$amount}")], $extra);

        return $this->update(values: $update);
    }

    /**
     * Atomically decrement a numeric field by a specific quantity.
     *
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/Mutations.md#upsert
     *
     * -- intent:
     * Execute a server-side subtraction from a specific column, ensuring
     * thread-safe value modification at the database level.
     *
     * @param string         $column The technical name of the numeric field to decrement.
     * @param int|float|null $amount The quantity to subtract (defaults to 1).
     * @param array          $extra  Optional additional fields to update simultaneously.
     *
     * @throws Throwable If the resulting SQL update execution fails.
     * @return bool True if the operation was successful.
     */
    public function decrement(string $column, int|float|null $amount = null, array $extra = []) : bool
    {
        $amount  ??= 1;
        $wrapped = $this->grammar->wrap(value: $column);
        $update  = array_merge([$column => $this->raw(value: "{$wrapped} - {$amount}")], $extra);

        return $this->update(values: $update);
    }
}
