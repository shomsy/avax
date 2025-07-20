<?php

declare(strict_types=1);

namespace Gemini\Database\Migration\Design\Table\Enum;

/**
 * Enum ForeignActionEnum
 *
 * This enum represents possible actions to be taken on foreign key constraints
 * when certain events occur in the referenced table, such as row deletions or updates.
 *
 * In the context of database migrations, this enum simplifies handling of foreign key behaviors
 * by providing a strongly typed definition for actions like cascade, restrict, or no action.
 * It enhances type-safety, readability, and ensures centralized management of foreign key options.
 *
 * Designed using PHP 8.1+ enums, this class leverages modern features for expressive and reliable
 * definition of constants.
 */
enum ForeignActionEnum: string // Enum declaration with 'string' type to ensure type safety for the defined cases.
{
    /**
     * Indicates cascading behavior for foreign keys.
     * When the referenced row is updated or deleted, the change cascades to the dependent rows.
     *
     * Example: If a parent record is removed, all associated child records are also removed.
     *
     * @var string
     */
    case CASCADE = 'CASCADE';

    /**
     * Indicates behavior to set foreign key columns to NULL.
     * When the referenced row is deleted, dependent foreign key columns in related rows are set to NULL.
     *
     * Example: If a parent record is deleted, the foreign key in child records will be nullified.
     *
     * @var string
     */
    case SET_NULL = 'SET NULL';

    /**
     * Restricts changes to the referenced row.
     * Prevents any changes (such as deletion) to a parent row when there are dependencies on it.
     *
     * Example: Trying to delete a parent record with dependent child records will raise an error.
     *
     * @var string
     */
    case RESTRICT = 'RESTRICT';

    /**
     * Indicates no action should be taken on foreign key constraints.
     * It simply allows the database to raise an error if the integrity rules are violated.
     *
     * Example: If a parent record is targeted for deletion but a child record exists, the operation fails.
     *
     * @var string
     */
    case NO_ACTION = 'NO ACTION';

    /**
     * Retrieves an array of all values defined by the enum cases.
     *
     * This method provides a centralized, type-safe way to access the raw string values
     * of all enum cases. It is useful when generating lists of possible options for migrations
     * or when working with foreign key actions dynamically.
     *
     * Uses PHP's built-in `cases()` method, introduced in PHP 8.1+, to retrieve the values of all cases.
     *
     * @return array<int, string> An indexed array containing the string values of all enum cases.
     */
    public static function values() : array
    {
        // Leverages `array_column` to extract the 'value' property from each enum case.
        return array_column(self::cases(), 'value');
    }
}