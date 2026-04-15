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
    public string|null $alias;
    public string      $name;

    /**
     * @param string      $name  The technical identifier of the database column.
     * @param string|null $alias The optional domain-specific label (alias) for the projection.
     */
    public function __construct(
        string      $name,
        string|null $alias = null
    )
    {
        $this->name  = $name;
        $this->alias = $alias;
    }

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
