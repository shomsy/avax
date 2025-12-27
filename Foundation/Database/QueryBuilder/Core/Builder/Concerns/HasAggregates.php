<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Core\Builder\Concerns;

use Throwable;

/**
 * Trait providing statistical and aggregate function capabilities for the QueryBuilder.
 *
 * -- intent:
 * Extends the QueryBuilder with a comprehensive Domain Specific Language (DSL) 
 * for performing server-side calculations (count, min, max, average, sum), 
 * optimizing data retrieval by aggregating results at the database level.
 *
 * -- invariants:
 * - Aggregate methods must return a single scalar value.
 * - Aggregate logic must use a cloned builder instance to prevent polluting the original query state.
 * - Results must be extracted from a virtual 'aggregate' column in the result set.
 *
 * -- boundaries:
 * - Does NOT handle complex window functions (delegated to raw expressions).
 * - Does NOT perform client-side calculation (all logic is SQL-based).
 */
trait HasAggregates
{
    /**
     * Retrieve the total count of records matching the current criteria.
     *
     * -- intent:
     * Provide a high-level shorthand for executing an SQL "COUNT(*)" or 
     * specific field aggregation to determine existence or volume.
     *
     * @param string $columns The specific technical field to target for counting (defaults to '*').
     * @throws Throwable If the query execution fails at the driver level.
     * @return int The total number of matching records found.
     */
    public function count(string $columns = '*'): int
    {
        return (int) $this->aggregate(function: 'count', columns: [$columns]);
    }

    /**
     * Retrieve the maximum value found in a specific field.
     *
     * -- intent:
     * Provide a high-level shorthand for executing an SQL "MAX(column)" 
     * aggregation across the current filtered dataset.
     *
     * @param string $column The technical field name whose peak value is required.
     * @throws Throwable If the query execution fails at the driver level.
     * @return mixed The highest scalar value found in the specified field.
     */
    public function max(string $column): mixed
    {
        return $this->aggregate(function: 'max', columns: [$column]);
    }

    /**
     * Retrieve the minimum value found in a specific field.
     *
     * -- intent:
     * Provide a high-level shorthand for executing an SQL "MIN(column)" 
     * aggregation across the current filtered dataset.
     *
     * @param string $column The technical field name whose lowest value is required.
     * @throws Throwable If the query execution fails at the driver level.
     * @return mixed The lowest scalar value found in the specified field.
     */
    public function min(string $column): mixed
    {
        return $this->aggregate(function: 'min', columns: [$column]);
    }

    /**
     * Calculate the average (mean) value for a specific numeric field.
     *
     * -- intent:
     * Provide a high-level shorthand for executing an SQL "AVG(column)" 
     * aggregation across the current filtered dataset.
     *
     * @param string $column The technical field name to target for averaging.
     * @throws Throwable If the query execution fails at the driver level.
     * @return mixed The calculated average value, or 0 if no records match.
     */
    public function avg(string $column): mixed
    {
        return $this->aggregate(function: 'avg', columns: [$column]);
    }

    /**
     * Calculate the cumulative sum of values in a specific numeric field.
     *
     * -- intent:
     * Provide a high-level shorthand for executing an SQL "SUM(column)" 
     * aggregation across the current filtered dataset.
     *
     * @param string $column The technical field name to target for summation.
     * @throws Throwable If the query execution fails at the driver level.
     * @return mixed The total cumulative sum calculated by the database server.
     */
    public function sum(string $column): mixed
    {
        return $this->aggregate(function: 'sum', columns: [$column]);
    }

    /**
     * Internal technician for executing a generic SQL aggregate function.
     *
     * -- intent:
     * Centralize the logic for query cloning, state modification, and scalar 
     * result extraction for all statistical aggregation operations.
     *
     * @param string $function The name of the SQL aggregate function (e.g., 'COUNT', 'SUM').
     * @param array  $columns  The technical field identifiers to target for the calculation.
     * @throws Throwable If the underlying query execution or result extraction fails.
     * @return mixed The resulting scalar data point retrieved from the aggregate projection.
     */
    protected function aggregate(string $function, array $columns = ['*']): mixed
    {
        $clone        = clone $this;
        $clone->state = $clone->state->withColumns(columns: [
            $this->raw(value: "{$function}(" . implode(separator: ', ', array: (array) $columns) . ") as aggregate")
        ]);

        $result = $clone->get();

        if (empty($result)) {
            return 0;
        }

        return $result[0]['aggregate'] ?? 0;
    }
}
