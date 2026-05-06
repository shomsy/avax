<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\State\AST;

/**
 * Immutable AST node representing a single WHERE condition.
 *
 * @see /docs/Foundation/Database/DSL/QueryStates.md
 */
final readonly class WhereNode
{
    public string $boolean;

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
        ?string $boolean = null,
        public string $type = 'Basic',
    ) {
        $boolean ??= 'AND';
        $this->boolean = $boolean;
    }
}
