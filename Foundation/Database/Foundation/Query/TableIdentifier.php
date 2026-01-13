<?php

declare(strict_types=1);

namespace Avax\Database\Query;

/**
 * Immutable value object representing a database table identifier with optional alias.
 *
 * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/QueryStates.md
 */
final readonly class TableIdentifier
{
    /**
     * @param string      $name  The technical identifier (physical name) of the database table.
     * @param string|null $alias The optional domain-specific label (alias) assigned to the table source.
     */
    public function __construct(
        public string      $name,
        public string|null $alias = null
    ) {}

    /**
     * Convert to SQL-like string format.
     */
    public function __toString() : string
    {
        if ($this->alias === null) {
            return $this->name;
        }

        return "{$this->name} AS {$this->alias}";
    }
}
