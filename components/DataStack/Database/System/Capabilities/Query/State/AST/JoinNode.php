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
    public string $type;

    /**
     * @param string          $table    The technical name of the target database table to be joined.
     * @param string          $type     The relational strategy for the join (e.g., 'inner', 'left', 'right', 'cross').
     * @param string|null     $first    The primary column label used in the comparison (Left-hand side).
     * @param string|null     $operator The SQL comparison operator (e.g., '=', '!=', 'LIKE').
     * @param string|null     $second   The secondary column label used in the comparison (Right-hand side).
     * @param JoinClause|null $clause   Optional container for complex, multi-condition join logic.
     */
    public function __construct(
        public string      $table,
        ?string            $type = null,
        public ?string     $first = null,
        public ?string     $operator = null,
        public ?string     $second = null,
        public ?JoinClause $clause = null,
    ) {
        $type ??= 'inner';
        $this->type = $type;
    }
}
