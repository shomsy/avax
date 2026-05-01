<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\Concerns;

use Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder;
use Avax\Components\DataStack\Database\System\Capabilities\Query\State\AST\OrderNode;

/**
 * Trait providing sorting and ordering capabilities for the QueryBuilder.
 *
 * @see /docs/Foundation/Database/DSL/Ordering.md
 */
trait HasOrders
{
    /**
     * Add a descending sorting criterion (ORDER BY ... DESC).
     *
     * @see /docs/Foundation/Database/DSL/Ordering.md#orderbydesc
     *
     * @param  string  $column  The technical field name to target for descending sort.
     * @return HasOrders|QueryBuilder A
     *                                fresh,
     *                                cloned
     *                                builder
     *                                instance
     *                                with
     *                                the
     *                                descending
     *                                order.
     */
    public function orderByDesc(string $column): self
    {
        return $this->orderBy(column: $column, direction: 'DESC');
    }

    /**
     * Add a primary sorting criterion (ORDER BY) to the current query context.
     *
     * @see /docs/Foundation/Database/DSL/Ordering.md#orderby
     *
     * @param  string  $column  The technical field name to target for sorting.
     * @param  string  $direction  The sorting orientation ('ASC' or 'DESC').
     * @return HasOrders|QueryBuilder A
     *                                fresh,
     *                                cloned
     *                                builder
     *                                instance
     *                                with
     *                                the
     *                                applied
     *                                order.
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $clone = clone $this;
        $clone->state = $clone->state->addOrder(order: new OrderNode(
            column   : $column,
            direction: strtoupper(string: $direction),
        ));

        return $clone;
    }

    /**
     * Sort the resulting records in a random sequence.
     *
     * @see /docs/Foundation/Database/DSL/Ordering.md#inrandomorder
     *
     * @return HasOrders|QueryBuilder A
     *                                fresh,
     *                                cloned
     *                                builder
     *                                instance
     *                                with
     *                                random
     *                                ordering
     *                                active.
     */
    public function inRandomOrder(): self
    {
        $clone = clone $this;
        $clone->state = $clone->state->addOrder(order: new OrderNode(
            sql : $this->grammar->compileRandomOrder(),
            type: 'Raw',
        ));

        return $clone;
    }

    /**
     * Sort the result set by the most recent records first.
     *
     * @see /docs/Foundation/Database/DSL/Ordering.md#latest
     *
     * @param  string  $column  The timestamp or sequence field to target (defaults to 'created_at').
     * @return HasOrders|QueryBuilder A
     *                                fresh,
     *                                cloned
     *                                builder
     *                                instance
     *                                sorted
     *                                by
     *                                newest
     *                                first.
     */
    public function latest(string $column = 'created_at'): self
    {
        return $this->orderBy(column: $column, direction: 'DESC');
    }

    /**
     * Sort the result set by the oldest records first.
     *
     * @see /docs/Foundation/Database/DSL/Ordering.md#oldest
     *
     * @param  string  $column  The timestamp or sequence field to target (defaults to 'created_at').
     * @return HasOrders|QueryBuilder A
     *                                fresh,
     *                                cloned
     *                                builder
     *                                instance
     *                                sorted
     *                                by
     *                                oldest
     *                                first.
     */
    public function oldest(string $column = 'created_at'): self
    {
        return $this->orderBy(column: $column, direction: 'ASC');
    }
}
