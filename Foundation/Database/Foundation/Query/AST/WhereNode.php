<?php

declare(strict_types=1);

namespace Avax\Database\Query\AST;

/**
 * Immutable AST node representing a single WHERE condition.
 *
 * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/QueryStates.md
 */
final readonly class WhereNode
{
    /**
     * @param  string  $column  The technical name of the field or a raw SQL fragment to be filtered.
     * @param  string  $operator  The SQL comparison operator (e.g., '=', '<>', 'LIKE', 'IS NULL').
     * @param  mixed  $value  The comparison target value, which may be a scalar, array, or null.
     * @param  string  $boolean  The logical joiner used to link this node ('AND' or 'OR').
     * @param  string  $type  The type classification of the constraint (e.g., 'Basic', 'Null', 'Raw').
     */
    public function __construct(
        public string $column,
        public string $operator,
        public mixed $value = null,
        public string $boolean = 'AND',
        public string $type = 'Basic'
    ) {}
}
