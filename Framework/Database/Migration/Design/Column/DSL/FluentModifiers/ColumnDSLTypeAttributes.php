<?php

declare(strict_types=1);

namespace Gemini\Database\Migration\Design\Column\DSL\FluentModifiers;

use Gemini\Database\Migration\Design\Column\DSL\ColumnDefinition;
use Gemini\Database\Migration\Design\Column\DTO\ColumnAttributesDTO;
use InvalidArgumentException;
use ReflectionException;

/**
 * Provides a fluent interface for column-type-specific attribute modifications.
 *
 * This trait encapsulates the type-specific modifiers for database column definitions,
 * implementing an immutable fluent interface pattern. It handles common column attributes
 * such as length, precision, scale, nullability, and unsigned specifications.
 *
 * @internal This trait is intended for internal use within the column definition system
 * @api      Exposes fluent interface methods for column type modifications
 *
 * @since    1.0.0
 */
trait ColumnDSLTypeAttributes
{
    /**
     * Defines the maximum length for character-based column types.
     *
     * Immutably creates a new column definition with the specified length attribute.
     * Commonly used for VARCHAR and CHAR column types.
     *
     * @param int<1, max> $length The maximum length of the column content
     *
     * @return ColumnDefinition A new immutable instance with the length attribute
     * @throws ReflectionException If reflection fails during DTO instantiation
     */
    public function length(int $length) : ColumnDefinition
    {
        return $this->withModifiedAttributes(modifiers: ['length' => $length]);
    }

    /**
     * Creates a new column definition with modified attributes.
     *
     * Internal helper method implementing the immutable modification pattern.
     * Clones the current state and applies new modifications to create a fresh instance.
     *
     * @param array<string, mixed> $modifiers Key-value pairs of attributes to modify
     *
     * @return ColumnDefinition A new immutable instance with applied modifications
     * @throws ReflectionException If reflection fails during DTO instantiation
     */
    protected function withModifiedAttributes(array $modifiers) : ColumnDefinition
    {
        // Extract current state as array
        $data = $this->getBuilder()->toArray();

        // Apply new modifications
        foreach ($modifiers as $key => $value) {
            $data[$key] = $value;
        }

        // Create a new immutable instance
        return new ColumnDefinition(dto: new ColumnAttributesDTO(data: $data));
    }

    /**
     * Configures precision and scale for decimal number columns.
     *
     * Creates a new column definition with specified numeric precision attributes.
     * Ensures proper relationship between precision and scale values.
     *
     * @param positive-int $precision Total number of significant digits
     * @param positive-int $scale     Number of digits after decimal point
     *
     * @return ColumnDefinition A new immutable instance with precision settings
     * @throws ReflectionException If reflection fails during DTO instantiation
     * @throws InvalidArgumentException If precision is less than scale
     */
    public function decimal(int $precision, int $scale) : ColumnDefinition
    {
        if ($precision < $scale) {
            throw new InvalidArgumentException(
                message: 'Precision must be greater than or equal to scale.'
            );
        }

        return $this->withModifiedAttributes(
            modifiers: [
                           'precision' => $precision,
                           'scale'     => $scale,
                       ]
        );
    }

    /**
     * Marks a numeric column as unsigned.
     *
     * Creates a new column definition with the unsigned flag set.
     * Applicable only to numeric column types.
     *
     * @return ColumnDefinition A new immutable instance marked as unsigned
     * @throws ReflectionException If reflection fails during DTO instantiation
     */
    public function unsigned() : ColumnDefinition
    {
        return $this->withModifiedAttributes(modifiers: ['unsigned' => true]);
    }

    /**
     * Marks a column as nullable.
     *
     * Creates a new column definition that allows NULL values.
     * This is a schema-level nullability setting.
     *
     * @return ColumnDefinition A new immutable instance marked as nullable
     * @throws ReflectionException If reflection fails during DTO instantiation
     */
    public function nullable() : ColumnDefinition
    {
        return $this->withModifiedAttributes(modifiers: ['nullable' => true]);
    }

    /**
     * Sets the precision (total number of digits) for numeric data types.
     *
     * This method follows the immutable modification pattern, creating a new instance
     * with the specified precision while preserving the original column definition.
     * Particularly useful for decimal, numeric, and floating-point data types.
     *
     * @param int $precision The total number of digits the column can store
     *
     * @return ColumnDefinition New immutable instance with updated precision
     * @throws \ReflectionException When reflection fails during DTO construction
     */
    public function precision(int $precision) : ColumnDefinition
    {
        // Create new immutable instance with updated precision attribute
        return $this->withModifiedAttributes(['precision' => $precision]);
    }

    /**
     * Sets the scale (number of decimal places) for numeric data types.
     *
     * This method implements the immutable modification pattern, creating a new instance
     * with the specified scale while maintaining immutability. Essential for decimal
     * and numeric data types where decimal precision is required.
     *
     * @param int $scale The number of digits after the decimal point
     *
     * @return ColumnDefinition New immutable instance with updated scale
     * @throws \ReflectionException When reflection fails during DTO construction
     */
    public function scale(int $scale) : ColumnDefinition
    {
        // Create a new immutable instance with updated scale attribute
        return $this->withModifiedAttributes(['scale' => $scale]);
    }

}