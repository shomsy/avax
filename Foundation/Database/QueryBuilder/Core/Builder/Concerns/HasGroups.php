<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Core\Builder\Concerns;

use Avax\Database\QueryBuilder\Core\Builder\QueryBuilder;

/**
 * Trait HasGroups
 *
 * -- intent: extend the query builder with aggregation grouping and filtering support.
 */
trait HasGroups
{
    /**
     * Add a GROUP BY clause to the query.
     *
     * -- intent: aggregate record sets based on one or more technical columns.
     *
     * @param string|array ...$columns Technical column identifiers
     *
     * @return HasGroups|QueryBuilder
     */
    public function groupBy(string|array ...$columns) : self
    {
        foreach ($columns as $column) {
            $this->state->groups = array_merge($this->state->groups, (array) $column);
        }

        return $this;
    }

    /**
     * Add a HAVING clause to the query.
     *
     * -- intent: filter aggregated record groups based on functional criteria.
     *
     * @param string $column   Technical column or aggregate function
     * @param string $operator Comparison operator
     * @param mixed  $value    Comparison target value
     * @param string $boolean  Logical joiner
     *
     * @return HasGroups|QueryBuilder
     */
    public function having(string $column, string $operator, mixed $value, string $boolean = 'AND') : self
    {
        $this->state->havings[] = compact('column', 'operator', 'value', 'boolean');

        $this->state->addBinding(value: $value);

        return $this;
    }
}
