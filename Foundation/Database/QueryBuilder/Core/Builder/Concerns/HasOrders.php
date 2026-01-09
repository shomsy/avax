<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Core\Builder\Concerns;

use Avax\Database\Query\AST\OrderNode;

/**
 * Trait providing sorting and ordering capabilities for the QueryBuilder.
 *
 * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/Ordering.md
 */
trait HasOrders
{
    /**
     * Add a descending sorting criterion (ORDER BY ... DESC).
     *
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/Ordering.md#orderbydesc
     *
     * @param string $column The technical field name to target for descending sort.
     *
     * @return \Avax\Database\QueryBuilder\Core\Builder\Concerns\HasOrders|\Avax\Database\QueryBuilder\Core\Builder\QueryBuilder A
     *                                                                                                                           fresh,
     *                                                                                                                           cloned
     *                                                                                                                           builder
     *                                                                                                                           instance
     *                                                                                                                           with
     *                                                                                                                           the
     *                                                                                                                           descending
     *                                                                                                                           order.
     */
    public function orderByDesc(string $column) : self
    {
        return $this->orderBy(column: $column, direction: 'DESC');
    }

    /**
     * Add a primary sorting criterion (ORDER BY) to the current query context.
     *
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/Ordering.md#orderby
     *
     * @param string $column    The technical field name to target for sorting.
     * @param string $direction The sorting orientation ('ASC' or 'DESC').
     *
     * @return \Avax\Database\QueryBuilder\Core\Builder\Concerns\HasOrders|\Avax\Database\QueryBuilder\Core\Builder\QueryBuilder A
     *                                                                                                                           fresh,
     *                                                                                                                           cloned
     *                                                                                                                           builder
     *                                                                                                                           instance
     *                                                                                                                           with
     *                                                                                                                           the
     *                                                                                                                           applied
     *                                                                                                                           order.
     */
    public function orderBy(string $column, string $direction = 'ASC') : self
    {
        $clone        = clone $this;
        $clone->state = $clone->state->addOrder(order: new OrderNode(
            column   : $column,
            direction: strtoupper(string: $direction)
        ));

        return $clone;
    }

    /**
     * Sort the resulting records in a random sequence.
     *
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/Ordering.md#inrandomorder
     *
     * @return \Avax\Database\QueryBuilder\Core\Builder\Concerns\HasOrders|\Avax\Database\QueryBuilder\Core\Builder\QueryBuilder A
     *                                                                                                                           fresh,
     *                                                                                                                           cloned
     *                                                                                                                           builder
     *                                                                                                                           instance
     *                                                                                                                           with
     *                                                                                                                           random
     *                                                                                                                           ordering
     *                                                                                                                           active.
     */
    public function inRandomOrder() : self
    {
        $clone        = clone $this;
        $clone->state = $clone->state->addOrder(order: new OrderNode(
            sql : $this->grammar->compileRandomOrder(),
            type: 'Raw'
        ));

        return $clone;
    }

    /**
     * Sort the result set by the most recent records first.
     *
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/Ordering.md#latest
     *
     * @param string $column The timestamp or sequence field to target (defaults to 'created_at').
     *
     * @return \Avax\Database\QueryBuilder\Core\Builder\Concerns\HasOrders|\Avax\Database\QueryBuilder\Core\Builder\QueryBuilder A
     *                                                                                                                           fresh,
     *                                                                                                                           cloned
     *                                                                                                                           builder
     *                                                                                                                           instance
     *                                                                                                                           sorted
     *                                                                                                                           by
     *                                                                                                                           newest
     *                                                                                                                           first.
     */
    public function latest(string $column = 'created_at') : self
    {
        return $this->orderBy(column: $column, direction: 'DESC');
    }

    /**
     * Sort the result set by the oldest records first.
     *
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/Ordering.md#oldest
     *
     * @param string $column The timestamp or sequence field to target (defaults to 'created_at').
     *
     * @return \Avax\Database\QueryBuilder\Core\Builder\Concerns\HasOrders|\Avax\Database\QueryBuilder\Core\Builder\QueryBuilder A
     *                                                                                                                           fresh,
     *                                                                                                                           cloned
     *                                                                                                                           builder
     *                                                                                                                           instance
     *                                                                                                                           sorted
     *                                                                                                                           by
     *                                                                                                                           oldest
     *                                                                                                                           first.
     */
    public function oldest(string $column = 'created_at') : self
    {
        return $this->orderBy(column: $column, direction: 'ASC');
    }
}
