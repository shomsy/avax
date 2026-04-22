<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\QueryBuilder\State;

/**
 * Immutable value object representing a database table identifier with optional alias.
 *
 * @see /docs/Foundation/Database/DSL/QueryStates.md
 */
final readonly class TableIdentifier
{
    public string|null $alias;
    public string      $name;

    /**
     * @param string      $name  The technical identifier (physical name) of the database table.
     * @param string|null $alias The optional domain-specific label (alias) assigned to the table source.
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
