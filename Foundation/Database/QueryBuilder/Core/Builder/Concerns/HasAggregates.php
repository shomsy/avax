<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Core\Builder\Concerns;

use Throwable;

/**
 * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/Aggregates.md
 */
trait HasAggregates
{
    /**
     * Retrieve the total count of records matching the current criteria.
     *
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/Aggregates.md#count
     *
     * @param string $columns The specific technical field to target for counting (defaults to '*').
     *
     * @throws Throwable If the query execution fails at the driver level.
     * @return int The total number of matching records found.
     */
    public function count(string $columns = '*') : int
    {
        return (int) $this->aggregate(function: 'count', columns: [$columns]);
    }

    /**
     * Internal technician for executing a generic SQL aggregate function.
     *
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/Aggregates.md
     *
     * @param string $function The name of the SQL aggregate function (e.g., 'COUNT', 'SUM').
     * @param array  $columns  The technical field identifiers to target for the calculation.
     *
     * @throws Throwable If the underlying query execution or result extraction fails.
     * @return mixed The resulting scalar data point retrieved from the aggregate projection.
     */
    protected function aggregate(string $function, array $columns = ['*']) : mixed
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

    /**
     * Retrieve the maximum value found in a specific field.
     *
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/Aggregates.md#max
     *
     * @param string $column The technical field name whose peak value is required.
     *
     * @throws Throwable If the query execution fails at the driver level.
     * @return mixed The highest scalar value found in the specified field.
     */
    public function max(string $column) : mixed
    {
        return $this->aggregate(function: 'max', columns: [$column]);
    }

    /**
     * Retrieve the minimum value found in a specific field.
     *
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/Aggregates.md#min
     *
     * @param string $column The technical field name whose lowest value is required.
     *
     * @throws Throwable If the query execution fails at the driver level.
     * @return mixed The lowest scalar value found in the specified field.
     */
    public function min(string $column) : mixed
    {
        return $this->aggregate(function: 'min', columns: [$column]);
    }

    /**
     * Calculate the average (mean) value for a specific numeric field.
     *
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/Aggregates.md#avg
     *
     * @param string $column The technical field name to target for averaging.
     *
     * @throws Throwable If the query execution fails at the driver level.
     * @return mixed The calculated average value, or 0 if no records match.
     */
    public function avg(string $column) : mixed
    {
        return $this->aggregate(function: 'avg', columns: [$column]);
    }

    /**
     * Calculate the cumulative sum of values in a specific numeric field.
     *
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/Aggregates.md#sum
     *
     * @param string $column The technical field name to target for summation.
     *
     * @throws Throwable If the query execution fails at the driver level.
     * @return mixed The total cumulative sum calculated by the database server.
     */
    public function sum(string $column) : mixed
    {
        return $this->aggregate(function: 'sum', columns: [$column]);
    }
}
