<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Core\Builder\Concerns;

use Avax\Database\Query\AST\OrderNode;

/**
 * Trait providing sorting and ordering capabilities for the QueryBuilder.
 *
 * -- intent:
 * Extends the QueryBuilder with a Domain Specific Language (DSL) for 
 * defining the sequence of retrieved result sets (ORDER BY), supporting 
 * both standard column sorting and specialized random or chronological patterns.
 *
 * -- invariants:
 * - Every ordering instruction must return a new, cloned builder instance.
 * - Ordering metadata must be encapsulated within an OrderNode abstraction.
 * - Supports sequential ordering instructions (multiple ORDER BY clauses).
 *
 * -- boundaries:
 * - Does NOT handle SQL compilation (delegated to Grammar).
 * - Does NOT validate the existence of the targeted sorting columns.
 */
trait HasOrders
{
    /**
     * Add a primary sorting criterion (ORDER BY) to the current query context.
     *
     * -- intent:
     * Specify the technical field and orientation for the result set 
     * sequence at the database level.
     *
     * @param string $column    The technical field name to target for sorting.
     * @param string $direction The sorting orientation ('ASC' or 'DESC').
     * @return self A fresh, cloned builder instance with the applied order.
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $clone        = clone $this;
        $clone->state = $clone->state->addOrder(order: new OrderNode(
            column: $column,
            direction: strtoupper(string: $direction)
        ));

        return $clone;
    }

    /**
     * Add a descending sorting criterion (ORDER BY ... DESC).
     *
     * -- intent:
     * Provide an expressive shorthand for reverse chronological or reverse 
     * numeric sorting patterns.
     *
     * @param string $column The technical field name to target for descending sort.
     * @return self A fresh, cloned builder instance with the descending order.
     */
    public function orderByDesc(string $column): self
    {
        return $this->orderBy(column: $column, direction: 'DESC');
    }

    /**
     * Sort the resulting records in a random sequence.
     *
     * -- intent:
     * Provide a pragmatic DSL for fetching unpredictable results, delegating 
     * the specific random function generation to the grammar dialect.
     *
     * @return self A fresh, cloned builder instance with random ordering active.
     */
    public function inRandomOrder(): self
    {
        $clone        = clone $this;
        $clone->state = $clone->state->addOrder(order: new OrderNode(
            sql: $this->grammar->compileRandomOrder(),
            type: 'Raw'
        ));

        return $clone;
    }

    /**
     * Sort the result set by the most recent records first.
     *
     * -- intent:
     * Provide a chronological shorthand for prioritizing recently updated or 
     * created domain records.
     *
     * @param string $column The timestamp or sequence field to target (defaults to 'created_at').
     * @return self A fresh, cloned builder instance sorted by newest first.
     */
    public function latest(string $column = 'created_at'): self
    {
        return $this->orderBy(column: $column, direction: 'DESC');
    }

    /**
     * Sort the result set by the oldest records first.
     *
     * -- intent:
     * Provide a chronological shorthand for prioritizing historical domain 
     * records across the current dataset.
     *
     * @param string $column The timestamp or sequence field to target (defaults to 'created_at').
     * @return self A fresh, cloned builder instance sorted by oldest first.
     */
    public function oldest(string $column = 'created_at'): self
    {
        return $this->orderBy(column: $column, direction: 'ASC');
    }
}
