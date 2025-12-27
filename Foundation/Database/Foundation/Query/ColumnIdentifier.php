<?php

declare(strict_types=1);

namespace Avax\Database\Query;

/**
 * Immutable value object representing a database column identifier with optional alias.
 *
 * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/QueryStates.md
 */
final readonly class ColumnIdentifier
{
    /**
     * @param string      $name  The technical identifier of the database column.
     * @param string|null $alias The optional domain-specific label (alias) for the projection.
     */
    public function __construct(
        public string      $name,
        public string|null $alias = null
    ) {}

    /**
     * Convert to SQL-like string format.
     *
     * @return string
     */
    public function __toString() : string
    {
        if ($this->alias === null) {
            return $this->name;
        }

        return "{$this->name} AS {$this->alias}";
    }
}
