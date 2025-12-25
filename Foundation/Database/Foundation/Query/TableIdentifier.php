<?php

declare(strict_types=1);

namespace Avax\Database\Query;

/**
 * Pragmatic value object representing a database table with an optional alias.
 *
 * -- intent: transport table identification metadata across the builder layers.
 */
final readonly class TableIdentifier
{
    /**
     * Constructor promoting immutable properties via PHP 8.3 features.
     *
     * -- intent: ensure data integrity for the table identifier.
     *
     * @param string      $name  Technical table identifier
     * @param string|null $alias Optional domain-specific alias (e.g. for joins)
     */
    public function __construct(
        public string      $name,
        public string|null $alias = null
    ) {}

    /**
     * Convert the table identifier into its SQL representation.
     *
     * -- intent: simplify SQL concatenation for table targeting.
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


