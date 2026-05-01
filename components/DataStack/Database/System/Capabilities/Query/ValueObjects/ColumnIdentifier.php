<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\ValueObjects;

use Stringable;

/**
 * Immutable value object representing a database column identifier with optional alias.
 *
 * @see /docs/Foundation/Database/DSL/QueryStates.md
 */
final readonly class ColumnIdentifier implements Stringable
{
    /**
     * @param string      $name  The technical identifier of the database column.
     * @param string|null $alias The optional domain-specific label (alias) for the projection.
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
