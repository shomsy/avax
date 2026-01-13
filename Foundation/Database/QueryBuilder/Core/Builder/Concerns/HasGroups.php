<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Core\Builder\Concerns;

/**
 * Trait providing aggregation grouping (GROUP BY) and aggregate filtering (HAVING) capabilities.
 *
 * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/Grouping.md
 *
 * -- boundaries:
 * - Does NOT handle SQL compilation (delegated to Grammar).
 * - Does NOT validate the technical correctness of aggregate functions used in HAVING.
 */
trait HasGroups
{
    /**
     * Consolidate the result set by one or more specific technical fields.
     *
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/Grouping.md#groupby
     *
     * @param  string|array  ...$columns  A variable list of field names or arrays of names to group by.
     * @return \Avax\Database\QueryBuilder\Core\Builder\Concerns\HasGroups|\Avax\Database\QueryBuilder\Core\Builder\QueryBuilder A
     *                                                                                                                           fresh,
     *                                                                                                                           cloned
     *                                                                                                                           builder
     *                                                                                                                           instance
     *                                                                                                                           with
     *                                                                                                                           the
     *                                                                                                                           grouping
     *                                                                                                                           criteria
     *                                                                                                                           applied.
     */
    public function groupBy(string|array ...$columns): self
    {
        $clone = clone $this;
        $groups = $this->state->groups;

        foreach ($columns as $column) {
            $groups = array_merge($groups, (array) $column);
        }

        $clone->state = $clone->state->withGroups(groups: $groups);

        return $clone;
    }

    /**
     * Apply a filtering criterion to aggregated groups in the result set.
     *
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/Grouping.md#having
     *
     * @param  string  $column  The technical column name or aggregate function expression to filter.
     * @param  string  $operator  The SQL comparison operator (e.g., '=', '>', '<').
     * @param  mixed  $value  The comparison target value, which will be safely parameterized.
     * @param  string  $boolean  The logical joiner used to attach this condition ('AND' or 'OR').
     * @return \Avax\Database\QueryBuilder\Core\Builder\Concerns\HasGroups|\Avax\Database\QueryBuilder\Core\Builder\QueryBuilder A
     *                                                                                                                           fresh,
     *                                                                                                                           cloned
     *                                                                                                                           builder
     *                                                                                                                           instance
     *                                                                                                                           with
     *                                                                                                                           the
     *                                                                                                                           aggregate
     *                                                                                                                           filter
     *                                                                                                                           applied.
     */
    public function having(string $column, string $operator, mixed $value, string $boolean = 'AND'): self
    {
        $clone = clone $this;
        $clone->state = $clone->state->addHaving(having: compact('column', 'operator', 'value', 'boolean'));
        $clone->state = $clone->state->addBinding(value: $value);

        return $clone;
    }
}
