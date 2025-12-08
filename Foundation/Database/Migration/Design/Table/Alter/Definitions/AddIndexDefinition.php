<?php

/**
 * Declares strict type checking for this file, ensuring type safety.
 */
declare(strict_types=1);

/**
 * Namespace for database migration design components focused on table alterations.
 */

namespace Avax\Database\Migration\Design\Table\Alter\Definitions;

use Avax\Database\Migration\Design\Table\Alter\Definitions\Base\AlterColumnDefinition;

/**
 * Represents an index creation operation in the database migration context.
 *
 * This value object encapsulates the logic for generating SQL statements
 * to create various types of database indexes (standard, unique, fulltext).
 * It follows immutable design principles to ensure consistency during
 * the migration process.
 *
 * @final    This class is not intended for inheritance
 * @readonly Ensures immutability of the object after construction
 */
final readonly class AddIndexDefinition extends AlterColumnDefinition
{
    /**
     * Valid index types supported by this definition.
     *
     * @var array<string>
     */
    private const array VALID_INDEX_TYPES = ['INDEX', 'UNIQUE', 'FULLTEXT'];

    /**
     * Initializes a new instance of the AddIndexDefinition value object.
     *
     * Uses constructor property promotion for concise and clean initialization
     * of the immutable properties.
     *
     * @param string        $name    The name of the index to be created
     * @param array<string> $columns The columns to be included in the index
     * @param string        $type    The type of index (INDEX, UNIQUE, FULLTEXT)
     */
    public function __construct(
        public string $name,
        public array  $columns,
        public string $type = 'INDEX'
    ) {
        assert(
            in_array($type, self::VALID_INDEX_TYPES, true),
            sprintf('Invalid index type. Must be one of: %s', implode(', ', self::VALID_INDEX_TYPES))
        );
    }

    /**
     * Generates the SQL statement for the index creation operation.
     *
     * Produces a standardized SQL CREATE INDEX statement with proper escaping
     * using backticks to prevent SQL injection and handle special characters
     * in column and index names.
     *
     * @return string The complete SQL statement for creating the index
     */
    public function toSql() : string
    {
        // Transform column names array into properly escaped column identifiers
        $columns = implode(
            ', ',
            array_map(
                static fn(string $col) : string => "`{$col}`",
                $this->columns
            )
        );

        // Construct the final SQL statement using the defined format
        return sprintf(
            '%s `%s` (%s)',
            strtoupper($this->type),
            $this->name,
            $columns
        );
    }
}