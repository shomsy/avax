<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\ValueObjects;

use Stringable;

/**
 * Immutable value object representing a database table identifier with optional alias.
 *
 * @see /docs/Foundation/Database/DSL/QueryStates.md
 */
final readonly class TableIdentifier implements Stringable
{
    /**
     * @param string      $name  The technical identifier (physical name) of the database table.
     * @param string|null $alias The optional domain-specific label (alias) assigned to the table source.
     */
    public function __construct(public string $name, public ?string $alias = null) {
    }

    /**
     * Convert to SQL-like string format.
     */
    public function __toString(): string
    {
        if ($this->alias === null) {
            return $this->name;
        }

        return sprintf('%s AS %s', $this->name, $this->alias);
    }
}
