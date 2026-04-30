<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\State\AST;

use Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\JoinClause;

/**
 * Immutable AST node representing a SQL JOIN operation.
 *
 * @see /docs/Foundation/Database/DSL/QueryStates.md
 */
final readonly class JoinNode
{
    public JoinClause|null $clause;
    public string|null $second;
    public string|null $operator;
    public string|null $first;
    public string      $type;
    public string      $table;

    /**
     * @param string          $table    The technical name of the target database table to be joined.
     * @param string          $type     The relational strategy for the join (e.g., 'inner', 'left', 'right', 'cross').
     * @param string|null     $first    The primary column label used in the comparison (Left-hand side).
     * @param string|null     $operator The SQL comparison operator (e.g., '=', '!=', 'LIKE').
     * @param string|null     $second   The secondary column label used in the comparison (Right-hand side).
     * @param JoinClause|null $clause   Optional container for complex, multi-condition join logic.
     */
    public function __construct(
        string     $table,
        string     $type = null,
        string     $first = null,
        string     $operator = null,
        string     $second = null,
        JoinClause|null $clause = null,
    )
    {
        $type ??= 'inner';
        $this->table    = $table;
        $this->type     = $type;
        $this->first    = $first;
        $this->operator = $operator;
        $this->second   = $second;
        $this->clause   = $clause;
    }
}
