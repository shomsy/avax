<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Core\Builder\Concerns;

/**
 * Trait providing aggregation grouping (GROUP BY) and aggregate filtering (HAVING) capabilities.
 *
 * -- intent:
 * Extends the QueryBuilder with a Domain Specific Language (DSL) for 
 * consolidating records based on shared attribute values and applying 
 * logical filters to the resulting aggregations.
 *
 * -- invariants:
 * - Every grouping or having addition must return a new, cloned builder instance.
 * - Grouping metadata must be accumulated within the QueryState's groups collection.
 * - Having parameters must be securely added to the QueryState's BindingBag.
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
     * -- intent:
     * Organize matching records into groups based on identical values in the 
     * specified columns, typically used for performing server-side aggregations.
     *
     * @param string|array ...$columns A variable list of field names or arrays of names to group by.
     * @return self A fresh, cloned builder instance with the grouping criteria applied.
     */
    public function groupBy(string|array ...$columns): self
    {
        $clone  = clone $this;
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
     * -- intent:
     * Provide a DSL for the SQL "HAVING" clause, allowing for logical 
     * filtering based on aggregate function results (e.g., HAVING count(*) > 5).
     *
     * @param string $column   The technical column name or aggregate function expression to filter.
     * @param string $operator The SQL comparison operator (e.g., '=', '>', '<').
     * @param mixed  $value    The comparison target value, which will be safely parameterized.
     * @param string $boolean  The logical joiner used to attach this condition ('AND' or 'OR').
     * @return self A fresh, cloned builder instance with the aggregate filter applied.
     */
    public function having(string $column, string $operator, mixed $value, string $boolean = 'AND'): self
    {
        $clone        = clone $this;
        $clone->state = $clone->state->addHaving(having: compact('column', 'operator', 'value', 'boolean'));
        $clone->state = $clone->state->addBinding(value: $value);

        return $clone;
    }
}
