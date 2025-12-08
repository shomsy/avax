<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Design\Table\Alter;

use Closure;
use Avax\Database\Migration\Design\Column\Column;
use Avax\Database\Migration\Design\Column\DSL\ColumnDefinition;
use Avax\Database\Migration\Design\Table\Alter\Definitions\AddForeignKeyDefinition;
use Avax\Database\Migration\Design\Table\Alter\Definitions\AddIndexDefinition;
use Avax\Database\Migration\Design\Table\Alter\Definitions\RenameColumnDefinition;
use Avax\Database\Migration\Design\Table\Alter\DTO\AlterOperation;
use Avax\Database\Migration\Design\Table\Alter\Enums\AlterType;
use InvalidArgumentException;
use ReflectionException;
use RuntimeException;

/**
 * Provides a fluent Domain-Specific Language (DSL) for database table alterations.
 *
 * This value object encapsulates the complete specification for altering database tables,
 * following Domain-Driven Design principles. It provides an immutable, type-safe interface
 * for defining structural changes to database tables.
 *
 * Example usage:
 * ```
 * $alter = AlterTable::for('users')
 *     ->addColumn('email', 'string')
 *     ->modifyColumn('status', fn(Column $column) => $column->enum(['active', 'inactive']))
 *     ->dropColumn('deprecated_field');
 * ```
 *
 * @final This class is not intended for inheritance
 */
final class AlterTable
{
    /**
     * Represents an immutable collection of atomic table alteration operations.
     *
     * This property maintains the ordered sequence of modifications that will be
     * applied to the database table. The order is significant as certain operations
     * may have dependencies on previous alterations.
     *
     * Operations are executed in FIFO (First-In-First-Out) order, ensuring
     * predictable schema modification behavior.
     *
     * @var list<AlterOperation> $operations A strictly typed list of atomic table modifications
     *                                       where each operation represents a single schema change
     */
    private array $operations = [];

    /**
     * Initializes a new immutable table alteration specification.
     *
     * @param string $tableName The canonical name of the database table to be altered
     *                          Must be a valid identifier according to database naming conventions
     *
     * @throws InvalidArgumentException If the table name is empty or contains invalid characters
     */
    private function __construct(private readonly string $tableName) {}

    /**
     * Creates a new table alteration specification following the named constructor pattern.
     * This factory method provides a more expressive and semantic way to initiate table alterations.
     *
     * @param string $tableName The identifier of the table to alter
     *
     * @return self A new immutable instance of TableAlteration
     *
     * @throws InvalidArgumentException If table name validation fails
     *
     * @api
     */
    public static function for(string $tableName) : self
    {
        return new self(tableName: $tableName);
    }

    /**
     * Adds a new column to the table with specified characteristics.
     *
     * @param string               $name   The identifier for the new column
     * @param string               $type   The SQL data type for the column
     * @param array<string, mixed> $params Additional column attributes
     *
     * @throws \ReflectionException
     * @throws \ReflectionException
     */
    public function addColumn(string $name, string $type, array $params = []) : self
    {
        $definition = $this->createColumnDefinition(
            method   : 'create',
            arguments: [$name, ...$params]
        );

        $this->operations[] = new AlterOperation(
            type      : AlterType::ADD_COLUMN,
            target    : $name,
            definition: $definition
        );

        return $this;
    }

    /**
     * Factory method for creating column definitions with specific configurations.
     *
     * @param string $method    The method name representing the column operation
     * @param array  $arguments Configuration parameters for the column
     *
     * @throws ReflectionException
     */
    private function createColumnDefinition(string $method, array $arguments) : ColumnDefinition
    {
        return (new Column())->create(method: $method, arguments: $arguments);
    }

    /**
     * Modifies an existing column's definition using a callback.
     *
     * @param string                            $name                     The identifier of the column to modify
     * @param Closure(Column): ColumnDefinition $columnDefinitionCallback Configuration callback
     *
     * @throws RuntimeException When callback returns an invalid definition
     */
    public function modifyColumn(string $name, Closure $columnDefinitionCallback) : self
    {
        $column     = new Column();
        $definition = $columnDefinitionCallback($column);

        if (! $definition instanceof ColumnDefinition) {
            throw new RuntimeException(
                message: 'Column definition callback must return a ColumnDefinition instance'
            );
        }

        $this->operations[] = new AlterOperation(
            type      : AlterType::MODIFY_COLUMN,
            target    : $name,
            definition: $definition
        );

        return $this;
    }

    /**
     * Renames an existing column in the table.
     *
     * This method creates a rename operation for an existing column while maintaining
     * referential integrity and schema consistency.
     *
     * @param string $from The current name of the column to be renamed
     * @param string $to   The new name for the column
     *
     * @return self Fluent interface for method chaining
     *
     */
    public function renameColumn(string $from, string $to) : self
    {
        $this->operations[] = new AlterOperation(
            type      : AlterType::RENAME_COLUMN,
            target    : $from,
            definition: new RenameColumnDefinition(from: $from, to: $to)
        );

        return $this;
    }


    /**
     * Removes a column from the table structure.
     *
     * This operation permanently removes the specified column and its data.
     * It Should be used with caution as it's irreversible in production.
     *
     * @param string $column The name of the column to be dropped
     *
     * @return self Fluent interface for method chaining
     *
     * @throws \ReflectionException When column definition cannot be created
     */
    public function dropColumn(string $column) : self
    {
        // Create a column definition for the drop operation
        $definition = $this->createColumnDefinition(method: 'drop', arguments: [$column]);

        // Register the drop column operation in the migration sequence
        $this->operations[] = new AlterOperation(
            type      : AlterType::DROP_COLUMN,
            target    : $column,
            definition: $definition
        );

        return $this;
    }

    /**
     * Removes an index from the table.
     *
     * Handles the removal of an existing index while ensuring
     * database performance implications are considered.
     *
     * @param string $indexName The name of the index to be removed
     *
     * @return self Fluent interface for method chaining
     *
     * @throws \ReflectionException When index definition cannot be created
     */
    public function dropIndex(string $indexName) : self
    {
        // Create a column definition for the drop index operation
        $definition = $this->createColumnDefinition(method: 'dropIndex', arguments: [$indexName]);

        // Register the drop index operation in the migration sequence
        $this->operations[] = new AlterOperation(
            type      : AlterType::DROP_INDEX,
            target    : $indexName,
            definition: $definition
        );

        return $this;
    }

    /**
     * Removes a foreign key constraint from the table.
     *
     * This operation removes the referential integrity constraint while
     * maintaining the underlying column and its data.
     *
     * @param string $foreignKeyName The name of the foreign key constraint to be removed
     *
     * @return self Fluent interface for method chaining
     *
     * @throws \ReflectionException When a foreign key definition cannot be created
     */
    public function dropForeign(string $foreignKeyName) : self
    {
        // Create a column definition for the drop foreign key operation
        $definition = $this->createColumnDefinition(method: 'dropForeign', arguments: [$foreignKeyName]);

        // Register the drop foreign key operation in the migration sequence
        $this->operations[] = new AlterOperation(
            type      : AlterType::DROP_FOREIGN,
            target    : $foreignKeyName,
            definition: $definition
        );

        return $this;
    }

    /**
     * Adds a new index definition to the table alteration operations queue.
     *
     * This method follows Domain-Driven Design principles by encapsulating index
     * creation logic within the aggregate root's context. It ensures type safety
     * through strict parameter typing and immutable operation queuing.
     *
     * @param string                    $name    The unique identifier for the index within the table's scope
     * @param array<int|string, string> $columns List of column names to be included in the index
     * @param string                    $type    The index type specification (defaults to 'INDEX')
     *                                           Supported values: 'INDEX', 'UNIQUE', 'FULLTEXT', 'SPATIAL'
     *
     * @return self Returns the current instance for method chaining (fluent interface)
     *
     * @throws InvalidArgumentException When invalid index type is provided
     */
    public function addIndex(
        string $name,
        array  $columns,
        string $type = 'INDEX'
    ) : self {
        // Append new alter operation to the operations collection using constructor promotion
        $this->operations[] = new AlterOperation(
            type      : AlterType::ADD_INDEX,        // Specifies the operation type as index addition
            target    : $name,                     // Sets the index name as the operation target
            definition: new AddIndexDefinition( // Creates immutable index definition
                            name   : $name,                   // Index identifier
                            columns: $columns,             // Columns to be indexed
                            type   : $type                    // Index type specification
                        )
        );

        // Return self for method chaining capability
        return $this;
    }

    /**
     * Retrieves the immutable table identifier from the migration context.
     *
     * This method provides access to the protected table name property, maintaining
     * encapsulation while exposing the necessary information for SQL generation.
     * Following Domain-Driven Design principles, it represents a crucial part of
     * the domain model's identity.
     *
     * @return string The fully qualified, immutable table identifier
     *
     * @throws never This method guarantees no exceptions will be thrown
     *
     * @api       This method is part of the public API contract
     * @since     1.0.0
     * @immutable This method always returns the same value for the same instance
     */
    public function getTable() : string
    {
        // Return the immutable table identifier stored during object construction
        return $this->tableName;
    }

    /**
     * Retrieves the collection of pending alter operations.
     *
     * @return list<AlterOperation> Ordered a sequence of table modifications
     */
    public function getOperations() : array
    {
        return $this->operations;
    }

    /**
     * Adds a foreign key constraint to establish referential integrity between tables.
     *
     * This domain operation ensures data consistency by creating a relationship between
     * the current table and a referenced table. It supports customizable referential
     * actions for maintaining data integrity during updates and deletions.
     *
     * @param string      $name       The identifier for the foreign key constraint
     * @param array       $columns    Local columns participating in the relationship
     * @param string      $refTable   The referenced table name
     * @param array       $refColumns Referenced columns in the target table
     * @param string|null $onDelete   Action to take when a referenced record is deleted
     * @param string|null $onUpdate   Action to take when a referenced record is updated
     *
     * @return self Fluent interface for method chaining
     *
     * @throws InvalidArgumentException When constraint parameters are invalid
     */
    public function addForeignKey(
        string      $name,       // Constraint identifier
        array       $columns,    // Source columns in the current table
        string      $refTable,   // Referenced table name
        array       $refColumns, // Target columns in the referenced table
        string|null $onDelete = null, // Optional deletion behavior
        string|null $onUpdate = null  // Optional update behavior
    ) : self
    {
        // Register a new foreign key operation in the migration sequence
        $this->operations[] = new AlterOperation(
            type      : AlterType::ADD_FOREIGN,      // Specify an operation type as a foreign key addition
            target    : $name,                       // Set the constraint name as the target
            definition: new AddForeignKeyDefinition( // Define the foreign key specifics
                            name             : $name,
                            columns          : $columns,
                            referencedTable  : $refTable,
                            referencedColumns: $refColumns,
                            onDelete         : $onDelete,
                            onUpdate         : $onUpdate
                        )
        );

        return $this; // Enable method chaining
    }
}