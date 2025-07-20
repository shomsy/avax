<?php

declare(strict_types=1);

namespace Gemini\Database\Migration\Design\Table\Alter\Enums;

/**
 * Enum AlterType
 *
 * Defines all supported ALTER TABLE operation types.
 * Used in conjunction with AlterOperation to describe mutations to a table schema.
 *
 * @immutable
 * @psalm-immutable
 */
enum AlterType: string
{
    /**
     * Adds a new column to a table.
     *
     * Example: ALTER TABLE users ADD COLUMN age INT;
     */
    case ADD_COLUMN = 'ADD_COLUMN';

    /**
     * Modifies an existing column.
     *
     * Example: ALTER TABLE users MODIFY COLUMN name VARCHAR(255) NOT NULL;
     */
    case MODIFY_COLUMN = 'MODIFY_COLUMN';

    /**
     * Renames a column.
     *
     * Example: ALTER TABLE users RENAME COLUMN old_name TO new_name;
     */
    case RENAME_COLUMN = 'RENAME_COLUMN';

    /**
     * Drops a column.
     *
     * Example: ALTER TABLE users DROP COLUMN deprecated_field;
     */
    case DROP_COLUMN = 'DROP_COLUMN';

    /**
     * Drops an index.
     *
     * Example: ALTER TABLE users DROP INDEX idx_email;
     */
    case DROP_INDEX = 'DROP_INDEX';

    /**
     * Drops a foreign key constraint.
     *
     * Example: ALTER TABLE orders DROP FOREIGN KEY fk_user_id;
     */
    case DROP_FOREIGN = 'DROP_FOREIGN';

    /**
     * Represents an operation to add a new index to a table.
     *
     * This operation allows the creation of different types of indexes (regular INDEX,
     * UNIQUE, FULLTEXT, SPATIAL) to optimize query performance and enforce data integrity.
     *
     * @api
     * @since 1.0.0
     * @example
     *     ALTER TABLE users ADD INDEX idx_email (email);
     *     ALTER TABLE users ADD UNIQUE INDEX idx_username (username);
     */
    case ADD_INDEX = 'ADD_INDEX';

    /**
     * Represents an operation to add a new foreign key constraint to a table.
     *
     * This operation establishes referential integrity between tables by creating
     * a foreign key relationship with configurable ON DELETE and ON UPDATE behaviors.
     *
     * @api
     * @since 1.0.0
     * @example
     *     ALTER TABLE orders
     *     ADD CONSTRAINT fk_user_id
     *     FOREIGN KEY (user_id)
     *     REFERENCES users(id)
     *     ON DELETE CASCADE
     *     ON UPDATE CASCADE;
     */
    case ADD_FOREIGN = 'ADD_FOREIGN';
}
