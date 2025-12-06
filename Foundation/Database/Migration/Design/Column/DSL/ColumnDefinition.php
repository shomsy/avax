<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Design\Column\DSL;

use Avax\Database\Migration\Design\Column\Builder\ColumnAttributes;
use Avax\Database\Migration\Design\Column\DSL\FluentModifiers\ColumnDSLKeyConstraints;
use Avax\Database\Migration\Design\Column\DSL\FluentModifiers\ColumnDSLSemantics;
use Avax\Database\Migration\Design\Column\DSL\FluentModifiers\ColumnDSLTypeAttributes;
use Avax\Database\Migration\Design\Column\DTO\ColumnAttributesDTO;
use Avax\Database\Migration\Design\Column\Enums\ColumnType;
use Avax\Database\Migration\Design\Column\Renderer\ColumnSQLRenderer;
use InvalidArgumentException;

/**
 * Represents a fluent interface for defining database column schemas.
 *
 * This value object encapsulates the definition of a database column using a Domain-Specific Language (DSL)
 * approach. It implements the Builder pattern through a fluent interface, allowing for expressive
 * and type-safe column definitions.
 *
 * @package   Avax\Database\Migration\Design\Column\DSL
 * @final     This class is not intended for extension
 * @immutable This class represents an immutable value object
 *
 *
 * ```php
 * $column = ColumnDefinition::make('user_id', ColumnType::INTEGER)
 *     ->unsigned()
 *     ->notNull()
 *     ->primary();
 * ```
 */
final class ColumnDefinition
{
    use ColumnDSLSemantics;
    use ColumnDSLKeyConstraints;
    use ColumnDSLTypeAttributes;

    /**
     * Encapsulates the internal state of the column definition.
     *
     * This property maintains the complete state of the column definition
     * through a dedicated value object, ensuring immutability and encapsulation
     * of the column attributes.
     *
     * @var ColumnAttributes
     */
    private ColumnAttributes $builder;

    /**
     * Initializes a new column definition instance.
     *
     * Constructs the column definition from a validated DTO containing
     * the essential column attributes. Uses constructor promotion for
     * clean and efficient initialization.
     *
     * @param ColumnAttributesDTO $dto Validated data transfer object containing column attributes
     */
    public function __construct(private readonly ColumnAttributesDTO $dto)
    {
        $this->builder = new ColumnAttributes(dto: $dto);
    }

    /**
     * Creates a new column definition using a fluent interface.
     *
     * Factory method implementing the Named Constructor pattern to provide
     * a more expressive way of creating column definitions.
     *
     * @param string     $name The logical name of the column
     * @param ColumnType $type The SQL data type for the column
     *
     * @return self New column definition instance
     * @throws \ReflectionException When reflection fails during DTO construction
     */
    public static function make(string $name, ColumnType $type) : self
    {
        return new self(
            dto: new ColumnAttributesDTO(
                     data: [
                               'name' => $name,
                               'type' => $type,
                           ]
                 )
        );
    }

    /**
     * Renders the column definition as an SQL string.
     *
     * Delegates the actual rendering to a dedicated renderer class,
     * following the Single Responsibility Principle.
     *
     * @return string The complete SQL column definition
     */
    public function __toString() : string
    {
        return ColumnSQLRenderer::render(column: $this->getBuilder());
    }

    /**
     * Retrieves the internal column attributes builder.
     *
     * Provides access to the underlying value object containing
     * the complete column definition state.
     *
     * @return ColumnAttributes The immutable column attributes value object
     */
    public function getBuilder() : ColumnAttributes
    {
        return $this->builder;
    }

    /**
     * Gets the logical name of the column.
     *
     * Provides direct access to the column's identifier without
     * exposing the internal builder implementation.
     *
     * @return string The column's identifier
     */
    public function columnName() : string
    {
        return $this->builder->name;
    }

    /**
     * Modifies the column definition by adding or updating column specifications.
     *
     * This method implements the immutable modification pattern, creating a new
     * instance with updated column specifications while preserving the original
     * definition. It follows the immutability principle essential for maintaining
     * a predictable state in domain-driven design.
     *
     * @param array<string, mixed> $columns New column specifications to be applied
     *
     * @return self New instance with updated column specifications
     * @throws \ReflectionException When reflection fails during DTO construction
     */

    public function columns(array $columns) : self
    {
        if (empty($columns)) {
            throw new InvalidArgumentException(message: 'Column names array cannot be empty.');
        }

        $this->validateColumnNames($columns);

        return $this->withModifiedAttributes(['columns' => $columns]);
    }

    /**
     * Validates the integrity of column names in a database schema definition.
     *
     * This method ensures that the provided column names meet the following criteria:
     * - Must be provided as a sequential array (list)
     * - Each element must be a non-empty string
     *
     * Part of the database schema validation layer that maintains data structure integrity.
     *
     * @param array<int, string> $columnNames Sequential array of column identifiers
     *
     * @throws InvalidArgumentException When validation fails due to invalid format or content
     */
    private function validateColumnNames(array $columnNames) : void
    {
        // Validate that the array is a sequential list and all elements are strings
        if (! array_is_list($columnNames)
            || array_filter($columnNames, static fn(mixed $column) : bool => ! is_string($column))
        ) {
            throw new InvalidArgumentException(
                message: 'All column names must be non-empty strings.'
            );
        }
    }
}