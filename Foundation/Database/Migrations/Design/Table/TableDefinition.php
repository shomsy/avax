<?php

declare(strict_types=1);

namespace Avax\Migrations\Design\Table;

/**
 * Technical value object representing the metadata of a database table.
 *
 * -- intent: transport structural table identifiers across the migration system.
 */
final readonly class TableDefinition
{
    /**
     * Constructor promoting the immutable table name via PHP 8.3 features.
     *
     * -- intent: capture the technical identity of a database table.
     *
     * @param  string  $name  Technical table identifier
     */
    public function __construct(
        public string $name
    ) {}
}
