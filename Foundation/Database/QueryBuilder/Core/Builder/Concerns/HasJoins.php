<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Core\Builder\Concerns;

use Avax\Database\Query\AST\JoinNode;
use Avax\Database\QueryBuilder\Core\Builder\JoinClause;
use Closure;

/**
 * Trait providing relational join capabilities for the QueryBuilder.
 *
 * -- intent:
 * Extends the QueryBuilder with a Domain Specific Language (DSL) for 
 * establishing relationships between different data sources (tables), 
 * allowing for complex relational retrieval and mutation.
 *
 * -- invariants:
 * - Every join addition must return a new, cloned builder instance.
 * - Join metadata must be encapsulated within a JoinNode abstraction.
 * - Supports both simple column-to-column comparisons and complex closure-based conditions.
 *
 * -- boundaries:
 * - Does NOT handle SQL compilation (delegated to Grammar).
 * - Does NOT perform schema verification for the target tables.
 */
trait HasJoins
{
    /**
     * Add an INNER JOIN clause to the current query context.
     *
     * -- intent:
     * Enforce a strict relational intersection between the primary source 
     * and the target table, retrieving only records that exist in both.
     *
     * @param string         $table    The technical name of the target database table to link.
     * @param string|Closure $first    The left-hand field name or a configuration closure for complex logic.
     * @param string|null    $operator The SQL comparison operator (defaults to '=' if second is provided).
     * @param string|null    $second   The right-hand field name belonging to the target table.
     * @return self A fresh, cloned builder instance with the inner join applied.
     */
    public function join(
        string         $table,
        string|Closure $first,
        string|null    $operator = null,
        string|null    $second = null
    ): self {
        return $this->addJoin(table: $table, first: $first, operator: $operator, second: $second, type: 'inner');
    }

    /**
     * Add a LEFT JOIN clause to the current query context.
     *
     * -- intent:
     * Retrieve all records from the primary source while optionally linking 
     * matching records from the secondary target table.
     *
     * @param string         $table    The technical name of the target database table to link.
     * @param string|Closure $first    The left-hand field name or a configuration closure.
     * @param string|null    $operator The SQL comparison operator.
     * @param string|null    $second   The right-hand field name.
     * @return self A fresh, cloned builder instance with the left join applied.
     */
    public function leftJoin(
        string         $table,
        string|Closure $first,
        string|null    $operator = null,
        string|null    $second = null
    ): self {
        return $this->addJoin(table: $table, first: $first, operator: $operator, second: $second, type: 'left');
    }

    /**
     * Add a RIGHT JOIN clause to the current query context.
     *
     * -- intent:
     * Retrieve all records from the secondary target table while optionally 
     * linking matching records from the primary source.
     *
     * @param string         $table    The technical name of the target database table to link.
     * @param string|Closure $first    The left-hand field name or a configuration closure.
     * @param string|null    $operator The SQL comparison operator.
     * @param string|null    $second   The right-hand field name.
     * @return self A fresh, cloned builder instance with the right join applied.
     */
    public function rightJoin(
        string         $table,
        string|Closure $first,
        string|null    $operator = null,
        string|null    $second = null
    ): self {
        return $this->addJoin(table: $table, first: $first, operator: $operator, second: $second, type: 'right');
    }

    /**
     * Add a CROSS JOIN clause to the current query context.
     *
     * -- intent:
     * Produce a Cartesian product of the primary source and the target table,
     * linking every record of one to every record of the other.
     *
     * @param string $table The technical name of the target database table to cross-link.
     * @return self A fresh, cloned builder instance with the cross join applied.
     */
    public function crossJoin(string $table): self
    {
        return $this->addJoin(table: $table, first: '', type: 'cross');
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
     * @return self A fresh, cloned builder instance containing the new join metadata.
     */
    protected function addJoin(
        string         $table,
        string|Closure $first,
        string|null    $operator = null,
        string|null    $second = null,
        string         $type = 'inner'
    ): self {
        $clone = clone $this;

        if ($first instanceof Closure) {
            $joinClause = new JoinClause(grammar: $this->grammar);
            $first($joinClause);

            $clone->state = $clone->state->addJoin(join: new JoinNode(
                table: $table,
                type: $type,
                clause: $joinClause
            ));
        } else {
            $clone->state = $clone->state->addJoin(join: new JoinNode(
                table: $table,
                type: $type,
                first: $first,
                operator: $operator,
                second: $second
            ));
        }

        return $clone;
    }
}
