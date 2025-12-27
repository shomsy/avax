<?php

declare(strict_types=1);

namespace Avax\Database\Query;

/**
 * Immutable value object encapsulating a single logical comparison constraint.
 *
 * @see docs/DSL/Filtering.md
 */
final readonly class Condition
{
    /**
     * @param string $column   The technical identifier of the database column to be filtered.
     * @param string $operator The logical comparison operator (e.g., '=', '<', '>', 'LIKE').
     * @param mixed  $value    The comparison target value (scalar, array, or expression).
     * @param string $boolean  The logical joiner used to link this condition ('AND' or 'OR').
     */
    public function __construct(
        public string $column,
        public string $operator,
        public mixed  $value,
        public string $boolean = 'AND'
    ) {}
}
