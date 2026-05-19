<?php

declare(strict_types=1);

namespace components\Database\System\Capabilities\Query\State\AST;

use components\Database\System\Capabilities\Query\Builder\QueryBuilder;

/**
 * Immutable AST node representing a nested logical grouping (SQL parentheses).
 *
 * @see /docs/Foundation/Database/DSL/QueryStates.md
 */
final readonly class NestedWhereNode
{
    public string $boolean;

    public QueryBuilder $query;

    /**
     * @param  QueryBuilder  $query  The localized builder instance containing the nested logical criteria.
     * @param  string  $boolean  The logical joiner used to attach this group to the outer query scope ('AND' or
     *                           'OR').
     */
    public function __construct(
        QueryBuilder $query,
        string $boolean = 'AND'
    ) {
        $this->query = $query;
        $this->boolean = $boolean;
    }
}
