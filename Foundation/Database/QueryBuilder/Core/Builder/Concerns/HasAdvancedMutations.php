<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Core\Builder\Concerns;

use Throwable;

/**
 * Trait HasAdvancedMutations
 *
 * -- intent: extend the query builder with complex data modification capabilities like Upsert and Increment.
 */
trait HasAdvancedMutations
{
    /**
     * Perform an "Upsert" operation: insert new records or update existing ones on conflict.
     *
     * -- intent: provide a high-level DSL for atomic mass-insert-or-update logic.
     *
     * @param array        $values   Dataset to insert
     * @param array|string $uniqueBy Columns that define a unique constraint
     * @param array|null   $update   Columns to update if a conflict occurs
     *
     * @return int Number of affected rows
     * @throws Throwable If SQL compilation or execution fails
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

        $this->state->values        = $values;
        $this->state->updateColumns = (array) $update;

        $sql = $this->grammar->compileUpsert(
            state   : $this->state,
            uniqueBy: (array) $uniqueBy,
            update  : $this->state->updateColumns
        );

        return $this->executor->execute(sql: $sql, bindings: $this->state->getBindings())->getAffectedRows();
    }

    /**
     * Increment the value of a specific column by a given amount.
     *
     * -- intent: provide a pragmatic shorthand for atomic numeric increments.
     *
     * @param string         $column Technical column name
     * @param int|float|null $amount Quantity to add
     * @param array          $extra  Additional columns to update simultaneously
     *
     * @return bool
     * @throws Throwable If SQL execution fails
     */
    public function increment(string $column, int|float|null $amount = null, array $extra = []) : bool
    {
        $amount  ??= 1;
        $wrapped = $this->grammar->wrap(value: $column);
        $update  = array_merge([$column => $this->raw(value: "{$wrapped} + {$amount}")], $extra);

        return $this->update(values: $update);
    }

    /**
     * Decrement the value of a specific column by a given amount.
     *
     * -- intent: provide a pragmatic shorthand for atomic numeric decrements.
     *
     * @param string         $column Technical column name
     * @param int|float|null $amount Quantity to subtract
     * @param array          $extra  Additional columns to update simultaneously
     *
     * @return bool
     * @throws Throwable If SQL execution fails
     */
    public function decrement(string $column, int|float|null $amount = null, array $extra = []) : bool
    {
        $amount  ??= 1;
        $wrapped = $this->grammar->wrap(value: $column);
        $update  = array_merge([$column => $this->raw(value: "{$wrapped} - {$amount}")], $extra);

        return $this->update(values: $update);
    }
}
