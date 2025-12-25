<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Core\Builder\Concerns;

use Avax\Database\QueryBuilder\Core\Builder\QueryBuilder;
use Closure;

/**
 * Trait HasJoins
 *
 * -- intent: extend the query builder with relational join capabilities.
 */
trait HasJoins
{
    /**
     * Add an INNER JOIN clause to the query.
     *
     * -- intent: provide a high-level DSL for standard relational record filtering.
     *
     * @param string         $table    Target database table
     * @param string|Closure $first    Left-hand column identifier
     * @param string|null    $operator Comparison operator
     * @param string|null    $second   Right-hand column identifier
     *
     * @return HasJoins|QueryBuilder
     */
    public function join(
        string         $table,
        string|Closure $first,
        string|null    $operator = null,
        string|null    $second = null
    ) : self
    {
        return $this->addJoin(table: $table, first: $first, operator: $operator, second: $second, type: 'inner');
    }

    /**
     * Internal technician for constructing various join definitions.
     *
     * -- intent: centralize join metadata collection for the grammar engine.
     *
     * @param string         $table    Target table
     * @param string|Closure $first    Comparison source
     * @param string|null    $operator Comparison operator
     * @param string|null    $second   Comparison target
     * @param string         $type     Join significance (inner/left/right/cross)
     *
     * @return HasJoins|QueryBuilder
     */
    protected function addJoin(
        string         $table,
        string|Closure $first,
        string|null    $operator = null,
        string|null    $second = null,
        string         $type = 'inner'
    ) : self
    {
        $this->state->joins[] = compact('table', 'first', 'operator', 'second', 'type');

        return $this;
    }

    /**
     * Add a LEFT JOIN clause to the query.
     *
     * -- intent: provide a high-level DSL for inclusive relational record retrieval.
     *
     * @param string         $table    Target database table
     * @param string|Closure $first    Left-hand column identifier
     * @param string|null    $operator Comparison operator
     * @param string|null    $second   Right-hand column identifier
     *
     * @return HasJoins|QueryBuilder
     */
    public function leftJoin(
        string         $table,
        string|Closure $first,
        string|null    $operator = null,
        string|null    $second = null
    ) : self
    {
        return $this->addJoin(table: $table, first: $first, operator: $operator, second: $second, type: 'left');
    }

    /**
     * Add a RIGHT JOIN clause to the query.
     *
     * -- intent: provide a high-level DSL for reverse relational record retrieval.
     *
     * @param string         $table    Target database table
     * @param string|Closure $first    Left-hand column identifier
     * @param string|null    $operator Comparison operator
     * @param string|null    $second   Right-hand column identifier
     *
     * @return HasJoins|QueryBuilder
     */
    public function rightJoin(
        string         $table,
        string|Closure $first,
        string|null    $operator = null,
        string|null    $second = null
    ) : self
    {
        return $this->addJoin(table: $table, first: $first, operator: $operator, second: $second, type: 'right');
    }

    /**
     * Add a CROSS JOIN clause to the query.
     *
     * -- intent: provide a high-level DSL for Cartesian product joins.
     *
     * @param string $table Target database table
     *
     * @return HasJoins|QueryBuilder
     */
    public function crossJoin(string $table) : self
    {
        return $this->addJoin(table: $table, first: '', type: 'cross');
    }
}
