<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Core\Builder\Concerns;

use Avax\Database\QueryBuilder\Core\Builder\QueryBuilder;

/**
 * Trait HasOrders
 *
 * -- intent: extend the query builder with sorting and ordering capabilities.
 */
trait HasOrders
{
    /**
     * Add a descending ORDER BY clause to the query.
     *
     * -- intent: provide a shorthand for reverse chronological or numeric sorting.
     *
     * @param string $column Technical column name
     *
     * @return HasOrders|QueryBuilder
     */
    public function orderByDesc(string $column) : self
    {
        return $this->orderBy(column: $column, direction: 'DESC');
    }

    /**
     * Add an ORDER BY clause to the query.
     *
     * -- intent: provide a human-readable DSL for sorting record sets.
     *
     * @param string $column    Technical column name
     * @param string $direction Sort orientation (ASC/DESC)
     *
     * @return HasOrders|QueryBuilder
     */
    public function orderBy(string $column, string $direction = 'ASC') : self
    {
        $this->state->orders[] = [
            'column'    => $column,
            'direction' => strtoupper(string: $direction)
        ];

        return $this;
    }

    /**
     * Add a random sorting order to the query.
     *
     * -- intent: provide a pragmatic shorthand for chaotic record retrieval.
     *
     * @return HasOrders|QueryBuilder
     */
    public function inRandomOrder() : self
    {
        $this->state->orders[] = [
            'type'   => 'Raw',
            'sql'    => $this->grammar->compileRandomOrder(),
            'values' => []
        ];

        return $this;
    }

    /**
     * Add a latest (chronological desc) sort order.
     *
     * -- intent: provide a domain-specific shorthand for recent record retrieval.
     *
     * @param string $column Name of the timestamp column
     *
     * @return HasOrders|QueryBuilder
     */
    public function latest(string $column = 'created_at') : self
    {
        return $this->orderBy(column: $column, direction: 'DESC');
    }

    /**
     * Add an oldest (chronological asc) sort order.
     *
     * -- intent: provide a domain-specific shorthand for historic record retrieval.
     *
     * @param string $column Name of the timestamp column
     *
     * @return HasOrders|QueryBuilder
     */
    public function oldest(string $column = 'created_at') : self
    {
        return $this->orderBy(column: $column, direction: 'ASC');
    }
}
