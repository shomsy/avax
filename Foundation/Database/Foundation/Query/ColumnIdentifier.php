<?php

declare(strict_types=1);

namespace Avax\Database\Query;

/**
 * Pragmatic value object representing a database column with optional alias.
 *
 * -- intent: transport column identification metadata across the builder layers.
 */
final readonly class ColumnIdentifier
{
    /**
     * Constructor promoting immutable properties via PHP 8.3 features.
     *
     * -- intent: ensure data integrity for the column identifier.
     *
     * @param string      $name  Technical column identifier
     * @param string|null $alias Optional domain-specific alias
     */
    public function __construct(
        public string      $name,
        public string|null $alias = null
    ) {}

    /**
     * Convert the identifier into a displayable string format.
     *
     * -- intent: simplify SQL concatenation via stringable interface.
     *
     * @return string
     */
    public function __toString() : string
    {
        if ($this->alias === null) {
            return $this->name;
        }

        // alias
        return "{$this->name} AS {$this->alias}";
    }
}


