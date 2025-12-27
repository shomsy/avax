<?php

declare(strict_types=1);

namespace Avax\Database\Query\AST;

use Avax\Database\QueryBuilder\Core\Builder\QueryBuilder;

/**
 * Immutable AST node representing a nested logical grouping (SQL parentheses).
 *
 * @see docs/DSL/QueryStates.md
 */
final readonly class NestedWhereNode
{
    /**
     * @param QueryBuilder $query   The localized builder instance containing the nested logical criteria.
     * @param string       $boolean The logical joiner used to attach this group to the outer query scope ('AND' or 'OR').
     */
    public function __construct(
        public QueryBuilder $query,
        public string       $boolean = 'AND'
    ) {}
}
