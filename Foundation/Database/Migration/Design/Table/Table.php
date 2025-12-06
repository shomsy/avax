<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Design\Table;

use BadMethodCallException;
use Avax\Database\Migration\Design\Column\Column;
use Avax\Database\Migration\Design\Column\DSL\ColumnDefinition;
use Avax\Database\Migration\Design\Table\Traits\FieldMappingTrait;
use Avax\Database\Migration\Design\Table\Traits\IndexDefinitionsTrait;
use Avax\Database\Migration\Design\Table\Traits\TableRenderDslTrait;
use Avax\Database\Migration\Design\Table\Traits\TableRenderSqlTrait;
use RuntimeException;

/**
 * @internal Auto-generated from ColumnType enum
 * @method ColumnDefinition bigInteger(string $name)
 * @method ColumnDefinition binary(string $name)
 * @method ColumnDefinition boolean(string $name)
 * @method ColumnDefinition char(string $name, int $length = 255)
 * @method ColumnDefinition date(string $name)
 * @method ColumnDefinition dateTime(string $name)
 * @method ColumnDefinition decimal(string $name, int $precision = 10, int $scale = 2)
 * @method ColumnDefinition double(string $name, int $precision = 10, int $scale = 2)
 * @method ColumnDefinition enum(string $name, array $allowed)
 * @method ColumnDefinition float(string $name, int $precision = 10, int $scale = 2)
 * @method ColumnDefinition foreignId(string $name)
 * @method ColumnDefinition foreignKey(string $name)
 * @method ColumnDefinition integer(string $name)
 * @method ColumnDefinition json(string $name)
 * @method ColumnDefinition jsonb(string $name)
 * @method ColumnDefinition longText(string $name)
 * @method ColumnDefinition mediumInteger(string $name)
 * @method ColumnDefinition mediumText(string $name)
 * @method ColumnDefinition morphs(string $name)
 * @method ColumnDefinition nullableMorphs(string $name)
 * @method ColumnDefinition nullableTimestamps(string $name)
 * @method ColumnDefinition set(string $name, array $allowed)
 * @method ColumnDefinition smallInteger(string $name)
 * @method ColumnDefinition string(string $name, int $length = 255)
 * @method ColumnDefinition text(string $name)
 * @method ColumnDefinition time(string $name)
 * @method ColumnDefinition timestamp(string $name)
 * @method ColumnDefinition tinyInteger(string $name)
 * @method ColumnDefinition tinyText(string $name)
 * @method ColumnDefinition unsignedBigInteger(string $name)
 * @method ColumnDefinition unsignedDecimal(string $name, int $precision = 10, int $scale = 2)
 * @method ColumnDefinition unsignedInteger(string $name)
 * @method ColumnDefinition unsignedMediumInteger(string $name)
 * @method ColumnDefinition unsignedSmallInteger(string $name)
 * @method ColumnDefinition unsignedTinyInteger(string $name)
 * @method ColumnDefinition uuid(string $name)
 * @method ColumnDefinition year(string $name)
 * @method void timestamps() Adds created_at and updated_at columns
 * @method void softDeletes() Adds deleted_at column for soft deletes
 * @method void rememberToken() Adds remember_token column for auth tokens
 */
final class Table
{
    /**
     * Import the IndexDefinitionsTrait which provides robust database index management capabilities.
     *
     * This trait encapsulates the domain logic for defining and managing various types of database indexes:
     * - Standard indexes (INDEX)
     * - Unique indexes (UNIQUE)
     * - Fulltext indexes (FULLTEXT)
     * - Spatial indexes (SPATIAL)
     * - Composite indexes (COMPOSITE)
     *
     * @see   \IndexDefinitionsTrait For complete index management functionality
     * @since 8.3.0
     */
    use IndexDefinitionsTrait;

    /**
     * Imports TableRenderSqlTrait, which provides SQL generation capabilities for table definitions.
     *
     * This trait is responsible for converting table definitions into valid SQL CREATE TABLE statements.
     * It works in conjunction with column definitions and rendering logic to produce
     * properly formatted SQL strings.
     *
     * @see TableRenderSqlTrait::toSql() For the main SQL generation method
     * @see ColumnSQLRenderer For the column-specific SQL rendering
     *
     * @api
     */
    use TableRenderSqlTrait;

    /**
     * Incorporates table rendering capabilities via Domain-Specific Language (DSL).
     *
     * This trait provides DSL generation functionality for database table definitions,
     * enabling fluent and declarative table schema specifications. It transforms
     * column definitions into a standardized DSL format suitable for database migrations.
     *
     * @see     ColumnDSLRenderer For the underlying DSL formatting logic
     * @see     ColumnAttributes For the column attribute specifications
     *
     * @author  Your Name <your.email@domain.com>
     * @package Database\Schema
     * @version 1.0.0
     */
    use TableRenderDslTrait;

    /**
     * Imports the FieldMappingTrait which provides essential field mapping capabilities for database schema
     * definitions.
     *
     * This trait encapsulates domain logic for mapping FieldDTO objects to table schema DSL,
     * implementing a flexible and extensible field mapping strategy pattern.
     *
     * Key responsibilities:
     * - Manages field-to-DSL mapper injection
     * - Provides fluent interface for field application
     * - Handles both single and batch field mapping operations
     *
     * @see   FieldToDslMapperInterface For the mapping strategy contract
     * @see   FieldDTO For the field data transfer object structure
     *
     * @since 8.3.0
     * @api
     */
    use FieldMappingTrait;

    /**
     * Collection of column definitions indexed by column name.
     *
     * Maintains the ordered set of columns that define the table structure,
     * ensuring column name uniqueness through associative array keys.
     *
     * @var array<string, ColumnDefinition>
     */
    private array $columns = [];

    /**
     * Constructs a new Table instance with the specified name.
     *
     * Uses constructor promotion for lean initialization of the immutable name property.
     */
    private function __construct(private readonly string $name) {}

    /**
     * Dynamic column type handler implementing the Schema DSL.
     *
     * Provides a fluent interface for column definition by delegating to the Column factory.
     * Method name becomes the column type, the first argument is expected to be the column name.
     *
     * @param string            $method    The column types to create
     * @param array<int, mixed> $arguments The column definition arguments
     *
     * @return ColumnDefinition              The created column definition
     * @throws BadMethodCallException        When a column type is invalid
     * @throws RuntimeException|\ReflectionException             When column creation fails
     */
    public function __call(string $method, array $arguments) : ColumnDefinition
    {
        $column = (new Column())->create(
            method   : $method,
            arguments: $arguments
        );

        return $this->addColumn(column: $column);
    }

    /**
     * Named constructor implementing the factory pattern for Table creation.
     *
     * Provides a semantic way to instantiate new Table objects while encapsulating
     * construction details.
     *
     * @param string $name The logical name of the table
     *
     * @return self       The constructed Table instance
     */
    public static function create(string $name) : self
    {
        return new self(name: $name);
    }

    /**
     * Adds a column definition to the table schema.
     *
     * Maintains the column collection while supporting method chaining for the fluent interface.
     *
     * @param ColumnDefinition $column The column definition to add
     *
     * @return ColumnDefinition        The added column definition
     */
    public function addColumn(ColumnDefinition $column) : ColumnDefinition
    {
        $this->columns[$column->columnName()] = $column;

        return $column;
    }

    /**
     * Replaces or adds a column definition in the schema.
     *
     * This method ensures atomic column replacement within the schema definition,
     * maintaining schema consistency and integrity. It follows the Single
     * Responsibility Principle by focusing solely on column replacement logic.
     *
     * @param ColumnDefinition $column The column definition to replace or add
     *
     * @return void
     * @throws RuntimeException When attempting to replace with an invalid column
     */
    public function replaceColumn(ColumnDefinition $column) : void
    {
        // Extract the column name from the definition for validation and indexing
        $name = $column->columnName();

        // Ensure column name validity to maintain schema integrity
        if (! $name) {
            throw new RuntimeException(
                message: "Column name must not be empty for replacement."
            );
        }

        // Perform atomic column replacement in the schema definition
        $this->columns[$name] = $column;
    }

    /**
     * Retrieves the table name.
     *
     * Value object accessor for the immutable table name property.
     *
     * @return string The logical table name
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Retrieves all column definitions.
     *
     * Provides read-only access to the complete collection of column definitions.
     *
     * @return array<string, ColumnDefinition> Column definitions indexed by name
     */
    public function getColumns() : array
    {
        return $this->columns;
    }

    /**
     * Retrieves all defined table indexes.
     *
     * This method provides access to the collection of indexes that have been
     * defined for the current table schema.
     * The indexes can include various types such as:
     * - Regular indexes (INDEX)
     * - Unique indexes (UNIQUE)
     * - Fulltext indexes (FULLTEXT)
     * - Spatial indexes (SPATIAL)
     *
     * @return array<string, ColumnDefinition> Array of index definitions keyed by index name
     *
     * @since 1.0.0
     * @api
     */
    public function getIndexes() : array
    {
        // Return the protected collection of index definitions
        return $this->indexes;
    }
}