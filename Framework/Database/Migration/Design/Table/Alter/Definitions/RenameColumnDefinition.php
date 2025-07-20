<?php

/**
 * Declares strict type checking for this file, ensuring type safety.
 */
declare(strict_types=1);

/**
 * Namespace for database migration design components focused on table alterations.
 */

namespace Gemini\Database\Migration\Design\Table\Alter\Definitions;

use Gemini\Database\Migration\Design\Table\Alter\Definitions\Base\AlterColumnDefinition;

/**
 * Represents a column renaming operation in the database migration context.
 *
 * This value object encapsulates the logic for generating SQL statements
 * to rename database columns. It's immutable by design to ensure data integrity
 * during the migration process.
 *
 * @final    This class is not intended for inheritance
 * @readonly Ensures immutability of the object after construction
 */
final readonly class RenameColumnDefinition extends AlterColumnDefinition
{
    /**
     * Initializes a new instance of the RenameColumnDefinition value object.
     *
     * Uses constructor property promotion for concise and clean initialization
     * of the immutable properties.
     *
     * @param string $from The current name of the column to be renamed
     * @param string $to   The new name for the column
     */
    public function __construct(
        public string $from,
        public string $to
    ) {}

    /**
     * Generates the SQL statement for the column renaming operation.
     *
     * Produces a standardized SQL RENAME COLUMN statement with proper escaping
     * using backticks to prevent SQL injection and handle special characters
     * in column names.
     *
     * @return string The complete SQL statement for renaming the column
     */
    public function toSql() : string
    {
        return sprintf('RENAME COLUMN `%s` TO `%s`', $this->from, $this->to);
    }
}