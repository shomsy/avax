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
 * Represents a foreign key creation operation in the database migration context.
 *
 * This value object encapsulates the logic for generating SQL statements
 * to create foreign key constraints. It follows immutable design principles
 * to ensure consistency during the migration process.
 *
 * @final    This class is not intended for inheritance
 * @readonly Ensures immutability of the object after construction
 */
final readonly class AddForeignKeyDefinition extends AlterColumnDefinition
{
    /**
     * Initializes a new instance of the AddForeignKeyDefinition value object.
     *
     * Uses constructor property promotion for concise and clean initialization
     * of the immutable properties.
     *
     * @param string        $name              The name of the foreign key constraint
     * @param array<string> $columns           The local columns participating in the foreign key
     * @param string        $referencedTable   The referenced table name
     * @param array<string> $referencedColumns The columns in the referenced table
     * @param string|null   $onDelete          The ON DELETE behavior (CASCADE, SET NULL, etc.)
     * @param string|null   $onUpdate          The ON UPDATE behavior (CASCADE, SET NULL, etc.)
     */
    public function __construct(
        public string      $name,
        public array       $columns,
        public string      $referencedTable,
        public array       $referencedColumns,
        public string|null $onDelete = null,
        public string|null $onUpdate = null
    ) {}

    /**
     * Generates the SQL statement for the foreign key creation operation.
     *
     * Produces a standardized SQL ADD CONSTRAINT statement with proper escaping
     * using backticks to prevent SQL injection and handle special characters
     * in table and column names.
     *
     * @return string The complete SQL statement for creating the foreign key constraint
     */
    public function toSql() : string
    {
        // Transform column names arrays into properly escaped column identifiers
        $columns = implode(', ', array_map(static fn(string $col) : string => "`{$col}`", $this->columns));
        $refs    = implode(', ', array_map(static fn(string $col) : string => "`{$col}`", $this->referencedColumns));

        // Construct the base foreign key constraint SQL
        $sql = "ADD CONSTRAINT `{$this->name}` FOREIGN KEY ({$columns}) REFERENCES `{$this->referencedTable}` ({$refs})";

        // Append ON DELETE clause if specified
        if ($this->onDelete) {
            $sql .= " ON DELETE {$this->onDelete}";
        }

        // Append ON UPDATE clause if specified
        if ($this->onUpdate) {
            $sql .= " ON UPDATE {$this->onUpdate}";
        }

        return $sql;
    }
}