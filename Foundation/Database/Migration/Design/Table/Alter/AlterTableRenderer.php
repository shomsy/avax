<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Design\Table\Alter;

use Avax\Database\Migration\Design\Column\DSL\ColumnDefinition;
use Avax\Database\Migration\Design\Column\Renderer\ColumnSQLRenderer;
use Avax\Database\Migration\Design\Table\Alter\DTO\AlterOperation;
use Avax\Database\Migration\Design\Table\Alter\Enums\AlterType;
use RuntimeException;

/**
 * Class AlterTableRenderer
 *
 * Renders valid SQL ALTER TABLE statements from structured DSL operations.
 * Encapsulates the rendering logic for each supported operation, delegating
 * to the proper rendering strategies or SQL renderers depending on a definition type.
 *
 * @final
 */
final class AlterTableRenderer
{
    /**
     * Renders an ALTER TABLE SQL statement from a structured table alteration specification.
     *
     * This service method transforms a domain-specific AlterTable object into a valid SQL ALTER TABLE
     * statement. It ensures proper SQL syntax and escaping while maintaining database schema integrity.
     *
     * @param AlterTable $alter The domain model representing table alterations
     *
     * @return string The fully formed SQL ALTER TABLE statement
     * @throws RuntimeException When attempting to render an empty alteration set
     *
     * @example
     * $SQL = SQLRenderer::render(
     *     AlterTable::for('users')->addColumn('email', 'VARCHAR(255)')
     * );
     */
    public static function render(AlterTable $alter) : string
    {
        // Retrieve the collection of atomic table operations from the alteration specification
        $operations = $alter->getOperations();

        // Validate that at least one operation has been defined
        if (empty($operations)) {
            throw new RuntimeException(
                message: "No ALTER TABLE operations defined for table '{$alter->getTable()}'."
            );
        }

        // Transform each operation into its corresponding SQL representation
        $segments = [];
        foreach ($operations as $operation) {
            $segments[] = self::renderOperation(operation: $operation);
        }

        // Compose the final ALTER TABLE statement with a proper table name escaping
        return sprintf(
            'ALTER TABLE `%s` %s;',
            $alter->getTable(),
            implode(', ', $segments)
        );
    }

    /**
     * Renders a single database alteration operation into its SQL representation.
     *
     * This method implements the Strategy pattern by mapping AlterType enum values
     * to their corresponding SQL syntax. It ensures type-safety through PHP 8.3's
     * enhanced type system and match expressions.
     *
     * @param AlterOperation $operation The alteration operation value object
     *
     * @return string                  The SQL fragment representing the operation
     * @throws RuntimeException        When encountering unsupported operation types
     */
    private static function renderOperation(AlterOperation $operation) : string
    {
        // Use match expression for type-safe operation mapping
        return match ($operation->type) {
            // Handle column addition with proper SQL syntax
            AlterType::ADD_COLUMN    => sprintf(
                'ADD COLUMN %s',
                self::renderDefinition(operation: $operation)
            ),

            // Handle column modification maintaining schema consistency
            AlterType::MODIFY_COLUMN => sprintf(
                'MODIFY COLUMN %s',
                self::renderDefinition(operation: $operation)
            ),

            // Handle column renaming with a proper identifier escaping
            AlterType::RENAME_COLUMN => sprintf(
                'RENAME COLUMN `%s` TO `%s`',
                $operation->target,
                self::assertColumnRenameTarget(operation: $operation)
            ),

            // Handle column removal with a proper identifier escaping
            AlterType::DROP_COLUMN   => sprintf(
                'DROP COLUMN `%s`',
                $operation->target
            ),

            // Handle index removal with a proper identifier escaping
            AlterType::DROP_INDEX    => sprintf(
                'DROP INDEX `%s`',
                $operation->target
            ),

            // Handle foreign key constraint removal
            AlterType::DROP_FOREIGN  => sprintf(
                'DROP FOREIGN KEY `%s`',
                $operation->target
            ),

            // Handle index and foreign key additions through definition renderer
            AlterType::ADD_INDEX,
            AlterType::ADD_FOREIGN   => self::renderDefinition(operation: $operation),

            // Handle unsupported operations with a descriptive exception
            default                  => throw new RuntimeException(
                message: "Unsupported ALTER operation type: {$operation->type->value}"
            ),
        };
    }

    /**
     * Renders an SQL definition from an AlterOperation using polymorphic behavior.
     *
     * This method implements the Strategy pattern by dynamically selecting the appropriate
     * rendering approach based on the definition type. It handles both direct column
     * definitions and SQL-renderable objects through a uniform interface.
     *
     * @param AlterOperation $operation The operation containing the definition to render
     *
     * @return string                   The SQL-safe string representation
     * @throws RuntimeException         When definition is missing or unsupported
     */
    private static function renderDefinition(AlterOperation $operation) : string
    {
        // Extract definition from operation for validation and processing
        $definition = $operation->definition;

        // Ensure definition exists before attempting to render
        if ($definition === null) {
            throw new RuntimeException(
                message: "Definition missing for operation type: {$operation->type->value}"
            );
        }

        // Handle ColumnDefinition using a dedicated renderer for complex column structures
        if ($definition instanceof ColumnDefinition) {
            return ColumnSQLRenderer::render(column: $definition->getBuilder());
        }

        // Process objects implementing SQL rendering capabilities through toSql() method
        if (method_exists($definition, 'toSql')) {
            return $definition->toSql();
        }

        // Throw exception for unsupported definition types
        throw new RuntimeException(
            message: sprintf(
                         'Cannot render alter operation [%s]: definition is not renderable.',
                         $operation->type->value
                     )
        );
    }

    /**
     * Validates and extracts the target column name for a rename operation.
     *
     * This method ensures type safety and semantic correctness of column rename operations
     * by validating that the provided operation contains a valid ColumnDefinition.
     * Following Domain-Driven Design principles, it enforces invariants at the domain boundary.
     *
     * @param AlterOperation $operation The alter operation containing the rename definition
     *
     * @return string The validated target column name
     * @throws RuntimeException When the operation definition is not a valid ColumnDefinition
     */
    private static function assertColumnRenameTarget(AlterOperation $operation) : string
    {
        // Extract the definition from the operation for validation
        $definition = $operation->definition;

        // Ensure type safety through runtime assertion of the definition type
        if (! ($definition instanceof ColumnDefinition)) {
            throw new RuntimeException(
                message: "Invalid rename operation definition – expected ColumnDefinition."
            );
        }

        // Extract and return the validated target column name
        return $definition->columnName();
    }
}
