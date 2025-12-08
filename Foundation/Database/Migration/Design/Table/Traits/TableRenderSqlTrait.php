<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Design\Table\Traits;

use Avax\Database\Migration\Design\Column\Renderer\ColumnSQLRenderer;
use RuntimeException;

/**
 * Trait TableRenderSqlTrait
 *
 * Provides SQL rendering capabilities for table definitions in the database migration context.
 * Implements Domain-Driven Design principles for table schema representation.
 *
 * @package Avax\Database\Migration\Design\Table\Traits
 */
trait TableRenderSqlTrait
{
    /**
     * Generates the SQL CREATE TABLE statement for the current table definition.
     *
     * Transforms the abstract table representation into a valid SQL statement,
     * handling both simple and nested column definitions. Ensures proper SQL
     * formatting with indentation and newlines for improved readability.
     *
     * @return string Complete SQL CREATE TABLE statement
     * @throws RuntimeException When no columns are defined for the table
     */
    public function toSql() : string
    {
        // Retrieve all column definitions from the table schema
        $columns = $this->getColumns();

        // Validate that the table has at least one column defined
        if (empty($columns)) {
            throw new RuntimeException(
                message: sprintf('No columns defined for table [%s]', $this->getName())
            );
        }

        // Initialize collection for SQL column definitions
        $lines = [];

        // Process each column definition, handling both single and nested columns
        foreach ($columns as $col) {
            if (is_array($col)) {
                // Handle nested column definitions (e.g., for compound indexes)
                foreach ($col as $nested) {
                    $lines[] = ColumnSQLRenderer::render(column: $nested->getBuilder());
                }
            } else {
                // Process single column definition
                $lines[] = ColumnSQLRenderer::render(column: $col->getBuilder());
            }
        }

        // Construct the complete CREATE TABLE statement with proper formatting
        $sql = sprintf(
            "CREATE TABLE `%s` (\n    %s\n)",
            $this->getName(),
            implode(",\n    ", $lines)
        );

        // Append semicolon to complete the SQL statement
        return $sql . ';';
    }
}