<?php

declare(strict_types=1);

namespace Avax\Database\Query\AST;

use Avax\Database\QueryBuilder\Core\Builder\JoinClause;

/**
 * Immutable AST node representing a SQL JOIN operation.
 *
 * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/QueryStates.md
 */
final readonly class JoinNode
{
    /**
     * @param string          $table    The technical name of the target database table to be joined.
     * @param string          $type     The relational strategy for the join (e.g., 'inner', 'left', 'right', 'cross').
     * @param string|null     $first    The primary column label used in the comparison (Left-hand side).
     * @param string|null     $operator The SQL comparison operator (e.g., '=', '!=', 'LIKE').
     * @param string|null     $second   The secondary column label used in the comparison (Right-hand side).
     * @param JoinClause|null $clause   Optional container for complex, multi-condition join logic.
     */
    public function __construct(
        public string          $table,
        public string          $type = 'inner',
        public string|null     $first = null,
        public string|null     $operator = null,
        public string|null     $second = null,
        public JoinClause|null $clause = null
    ) {}
}
