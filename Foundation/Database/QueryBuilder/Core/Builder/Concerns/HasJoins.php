<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Core\Builder\Concerns;

use Avax\Database\Query\AST\JoinNode;
use Avax\Database\QueryBuilder\Core\Builder\JoinClause;
use Closure;

/**
 * Trait providing relational join capabilities for the QueryBuilder.
 *
 * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/Joins.md
 */
trait HasJoins
{
    /**
     * Add an INNER JOIN clause to the current query context.
     *
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/Joins.md#join
     *
     * @param string         $table    The technical name of the target database table to link.
     * @param string|Closure $first    The left-hand field name or a configuration closure for complex logic.
     * @param string|null    $operator The SQL comparison operator (defaults to '=' if second is provided).
     * @param string|null    $second   The right-hand field name belonging to the target table.
     *
     * @return \Avax\Database\QueryBuilder\Core\Builder\Concerns\HasJoins|\Avax\Database\QueryBuilder\Core\Builder\QueryBuilder A
     *                                                                                                                          fresh,
     *                                                                                                                          cloned
     *                                                                                                                          builder
     *                                                                                                                          instance
     *                                                                                                                          with
     *                                                                                                                          the
     *                                                                                                                          inner
     *                                                                                                                          join
     *                                                                                                                          applied.
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
     * Internal technician for constructing and registering join definitions.
     *
     * -- intent:
     * Centralize the construction of JoinNode abstractions and their registration
     * within the QueryState container, handling both simple and complex signatures.
     *
     * @param string         $table    The technical identifier of the target table.
     * @param string|Closure $first    Condition column label or a logic configuration closure.
     * @param string|null    $operator The comparison operator used in the ON clause.
     * @param string|null    $second   The target comparison field label.
     * @param string         $type     The join strategy type (inner/left/right/cross).
     *
     * @return \Avax\Database\QueryBuilder\Core\Builder\Concerns\HasJoins|\Avax\Database\QueryBuilder\Core\Builder\QueryBuilder A
     *                                                                                                                          fresh,
     *                                                                                                                          cloned
     *                                                                                                                          builder
     *                                                                                                                          instance
     *                                                                                                                          containing
     *                                                                                                                          the
     *                                                                                                                          new
     *                                                                                                                          join
     *                                                                                                                          metadata.
     */
    protected function addJoin(
        string         $table,
        string|Closure $first,
        string|null    $operator = null,
        string|null    $second = null,
        string         $type = 'inner'
    ) : self
    {
        $clone = clone $this;

        if ($first instanceof Closure) {
            $joinClause = new JoinClause(grammar: $this->grammar);
            $first($joinClause);

            $clone->state = $clone->state->addJoin(join: new JoinNode(
                table : $table,
                type  : $type,
                clause: $joinClause
            ));
        } else {
            $clone->state = $clone->state->addJoin(join: new JoinNode(
                table   : $table,
                type    : $type,
                first   : $first,
                operator: $operator,
                second  : $second
            ));
        }

        return $clone;
    }

    /**
     * Add a LEFT JOIN clause to the current query context.
     *
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/Joins.md#leftjoin
     *
     * @param string         $table    The technical name of the target database table to link.
     * @param string|Closure $first    The left-hand field name or a configuration closure.
     * @param string|null    $operator The SQL comparison operator.
     * @param string|null    $second   The right-hand field name.
     *
     * @return \Avax\Database\QueryBuilder\Core\Builder\Concerns\HasJoins|\Avax\Database\QueryBuilder\Core\Builder\QueryBuilder A
     *                                                                                                                          fresh,
     *                                                                                                                          cloned
     *                                                                                                                          builder
     *                                                                                                                          instance
     *                                                                                                                          with
     *                                                                                                                          the
     *                                                                                                                          left
     *                                                                                                                          join
     *                                                                                                                          applied.
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
     * Add a RIGHT JOIN clause to the current query context.
     *
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/Joins.md#rightjoin
     *
     * @param string         $table    The technical name of the target database table to link.
     * @param string|Closure $first    The left-hand field name or a configuration closure.
     * @param string|null    $operator The SQL comparison operator.
     * @param string|null    $second   The right-hand field name.
     *
     * @return \Avax\Database\QueryBuilder\Core\Builder\Concerns\HasJoins|\Avax\Database\QueryBuilder\Core\Builder\QueryBuilder A
     *                                                                                                                          fresh,
     *                                                                                                                          cloned
     *                                                                                                                          builder
     *                                                                                                                          instance
     *                                                                                                                          with
     *                                                                                                                          the
     *                                                                                                                          right
     *                                                                                                                          join
     *                                                                                                                          applied.
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
     * Add a CROSS JOIN clause to the current query context.
     *
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/Joins.md#crossjoin
     *
     * @param string $table The technical name of the target database table to cross-link.
     *
     * @return \Avax\Database\QueryBuilder\Core\Builder\Concerns\HasJoins|\Avax\Database\QueryBuilder\Core\Builder\QueryBuilder A
     *                                                                                                                          fresh,
     *                                                                                                                          cloned
     *                                                                                                                          builder
     *                                                                                                                          instance
     *                                                                                                                          with
     *                                                                                                                          the
     *                                                                                                                          cross
     *                                                                                                                          join
     *                                                                                                                          applied.
     */
    public function crossJoin(string $table) : self
    {
        return $this->addJoin(table: $table, first: '', type: 'cross');
    }
}
