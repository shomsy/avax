<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Core\Builder\Concerns;

use Throwable;

/**
 * Trait HasAggregates
 *
 * -- intent: extend the query builder with statistical and aggregate function support.
 */
trait HasAggregates
{
    /**
     * Retrieve the total count of records matching the current query state.
     *
     * -- intent: provide a high-level DSL for the SQL COUNT function.
     *
     * @param string $columns Technical column identifier to count
     *
     * @return int
     * @throws Throwable If SQL execution fails
     */
    public function count(string $columns = '*') : int
    {
        return (int) $this->aggregate(function: 'count', columns: [$columns]);
    }

    /**
     * Execute a generic aggregate function and return the scalar result.
     *
     * -- intent: coordinate state modification and execution for aggregate queries.
     *
     * @param string $function Technical function name (COUNT/SUM/etc)
     * @param array  $columns  Target columns
     *
     * @return mixed
     * @throws Throwable If execution fails
     */
    protected function aggregate(string $function, array $columns = ['*']) : mixed
    {
        $this->state->columns = [
            $this->raw(value: "{$function}(" . implode(separator: ', ', array: (array) $columns) . ") as aggregate")
        ];

        $result = $this->get();

        if (empty($result)) {
            return 0;
        }

        return $result[0]['aggregate'] ?? 0;
    }

    /**
     * Retrieve the maximum value for a specific column.
     *
     * -- intent: provide a high-level DSL for the SQL MAX function.
     *
     * @param string $column Target technical column
     *
     * @return mixed
     * @throws Throwable If SQL execution fails
     */
    public function max(string $column) : mixed
    {
        return $this->aggregate(function: 'max', columns: [$column]);
    }

    /**
     * Retrieve the minimum value for a specific column.
     *
     * -- intent: provide a high-level DSL for the SQL MIN function.
     *
     * @param string $column Target technical column
     *
     * @return mixed
     * @throws Throwable If SQL execution fails
     */
    public function min(string $column) : mixed
    {
        return $this->aggregate(function: 'min', columns: [$column]);
    }

    /**
     * Retrieve the average value for a specific column.
     *
     * -- intent: provide a high-level DSL for the SQL AVG function.
     *
     * @param string $column Target technical column
     *
     * @return mixed
     * @throws Throwable If SQL execution fails
     */
    public function avg(string $column) : mixed
    {
        return $this->aggregate(function: 'avg', columns: [$column]);
    }

    /**
     * Retrieve the sum of all values for a specific column.
     *
     * -- intent: provide a high-level DSL for the SQL SUM function.
     *
     * @param string $column Target technical column
     *
     * @return mixed
     * @throws Throwable If SQL execution fails
     */
    public function sum(string $column) : mixed
    {
        return $this->aggregate(function: 'sum', columns: [$column]);
    }
}
