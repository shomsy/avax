<?php

declare(strict_types=1);

namespace Avax\Database\Query;

/**
 * Pragmatic value object encapsulating a single SQL WHERE constraint.
 *
 * -- intent: transport logical comparison criteria to the SQL grammar engine.
 */
final readonly class Condition
{
    /**
     * Constructor promoting immutable criteria properties via PHP 8.3 features.
     *
     * -- intent: capture the full state of a logical query filter.
     *
     * @param string $column   Technical column identifier
     * @param string $operator Logical comparison operator (=, <, >, etc)
     * @param mixed  $value    Comparison target value
     * @param string $boolean  Logical joiner (AND/OR)
     */
    public function __construct(
        public string $column,
        public string $operator,
        public mixed  $value,
        public string $boolean = 'AND'
    ) {}
}


