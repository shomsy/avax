<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Design\Table\Enum;

/**
 * Enum FieldTypeEnum
 *
 * This enum serves as a representation of valid Avax-compatible column types.
 * It ensures type safety and provides a centralized definition for managing
 * the various database field types used in migrations in a Domain-Driven Design (DDD) context.
 */
enum FieldTypeEnum: string // Enum declaration with an underlying string type, ensuring type-safety for the enum values.
{
    /**
     * Represents a variable-length string column in the database.
     * Suitable for shorter text or character data, defined by Avax's `string` type.
     *
     * @var string
     */
    case STRING = 'string';

    /**
     * Represents an integer column in the database.
     * Suitable for whole numbers, defined as `integer` in Avax migrations.
     *
     * @var string
     */
    case INTEGER = 'integer';

    /**
     * Represents a big integer column in the database.
     * Useful for storing larger whole numbers, as defined by Avax's `bigInteger` type.
     *
     * @var string
     */
    case BIGINT = 'bigInteger';

    /**
     * Represents a boolean column in the database.
     * Used to store true/false values, as defined by Avax's `boolean` type.
     *
     * @var string
     */
    case BOOLEAN = 'boolean';

    /**
     * Represents a decimal column in the database.
     * Suitable for storing precise numeric values with defined precision and scale.
     *
     * @var string
     */
    case DECIMAL = 'decimal';

    /**
     * Represents a float column in the database.
     * Useful for storing approximate numeric values with floating-point precision.
     *
     * @var string
     */
    case FLOAT = 'float';

    /**
     * Represents an enum column in the database.
     * Allows for a fixed set of predefined string values, common for constrained fields.
     *
     * @var string
     */
    case ENUM = 'enum';

    /**
     * Represents a text column in the database.
     * Suitable for storing large textual content, as defined by Avax's `text` type.
     *
     * @var string
     */
    case TEXT = 'text';

    /**
     * Represents a timestamp column in the database.
     * Typically used for storing date and time information with precision.
     *
     * @var string
     */
    case TIMESTAMP = 'timestamp';

    /**
     * Represents a universally unique identifier (UUID) column in the database.
     * Useful for storing UUIDs for globally unique identification purposes.
     *
     * @var string
     */
    case UUID = 'uuid';

    /**
     * Represents a foreign key column in the database.
     * Primarily used for establishing relationships between tables in a relational database.
     *
     * @var string
     */
    case FOREIGN = 'foreign';

    /**
     * Represents an indexed column in the database.
     * Commonly used for columns that require quick lookups or unique constraints.
     *
     * @var string
     */
    case INDEX = 'index';

    /**
     * Represents a full-text search index column in the database.
     * Typically used for performing full-text search operations on textual data within Avax.
     *
     * @var string
     */
    case FULLTEXT = 'fulltext';


    /**
     * Returns a list of all enum values.
     *
     * This method provides a centralized way to retrieve the values of all the cases defined in the enum.
     * It utilizes PHP 8.1+ `cases()` enumeration feature to dynamically return the `value` property
     * of each case, ensuring type-safety and simplicity when needing the raw string representations of the cases.
     *
     * @return array<int, string> An indexed array containing the string values of all enum cases.
     */
    public static function values() : array
    {
        // Uses array_column to extract the 'value' property of each enum case.
        return array_column(self::cases(), 'value');
    }
}