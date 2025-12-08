<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Design\Table\Enum;

/**
 * Enumeration: FieldModifierEnum
 *
 * This enum provides a type-safe way to define and manage field modifiers
 * used in database migrations for the Avax system.
 * Each enumerated case represents a specific modifier,
 * ensuring maintainability and reducing duplication across the codebase.
 *
 * Following Domain-Driven Design (DDD), it encapsulates behavior related
 * to its enumeration, ensuring that valid operations are directly associated
 * with the definition itself.
 */
enum FieldModifierEnum: string
{
    /**
     * Represents the "nullable" field modifier.
     *
     * This modifier allows the associated database column to accept NULL values.
     *
     * @var string
     */
    case NULLABLE = 'nullable';

    /**
     * Represents the "unique" field modifier.
     *
     * This modifier ensures that all values in the associated database column
     * are unique and no duplicates are allowed.
     *
     * @var string
     */
    case UNIQUE = 'unique';

    /**
     * Represents the "primary" field modifier.
     *
     * This modifier signifies that the database column serves as a primary key,
     * which uniquely identifies each row in the table.
     *
     * @var string
     */
    case PRIMARY = 'primary';

    /**
     * Represents the "index" field modifier.
     *
     * This modifier designates the creation of an index for the associated column
     * to improve query performance.
     *
     * @var string
     */
    case INDEX = 'index';

    /**
     * Check if the provided value is a valid case for this enum.
     *
     * This method ensures that the given value matches one of the enum's predefined
     * cases, improving type safety and reducing unexpected errors during runtime.
     *
     * @param string $value The value to validate against the enum cases.
     *
     * @return bool Returns `true` if the value exists in the enum, otherwise `false`.
     */
    public static function isValid(string $value) : bool
    {
        // Validate if the provided value exists within the enum's list of values using strict comparison.
        return in_array($value, self::values(), true);
    }

    /**
     * Retrieve all string values of the enum cases.
     *
     * This method provides a centralized way to access the raw underlying values
     * of the defined enum cases. This is particularly useful when the raw values
     * need to be passed to external systems or stored in a database.
     *
     * @return array<int, string> An indexed array of the string values of all cases.
     */
    public static function values() : array
    {
        // Use PHP 8.1+ `cases()` method to get all enum cases and extract their `value` property.
        return array_column(self::cases(), 'value');
    }

    /**
     * Safely attempts to retrieve an enum instance from a given value. If the value is null
     * or invalid, it returns null instead of throwing an error.
     *
     * This method wraps around PHP's built-in `tryFrom()` to provide a safe and null-tolerant
     * implementation that prevents exceptions when handling dynamic inputs.
     *
     * @param string|null $value The value to convert to an enum instance, or `null`.
     *
     * @return self|null Returns the enum instance for the corresponding value, or `null` if the value is invalid.
     */
    public static function fromOrNull(string|null $value) : self|null
    {
        // Ensure type-safety by checking if the input is a string before attempting conversion.
        return is_string($value) ? self::tryFrom($value) : null;
    }
}