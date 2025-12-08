<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Design\Table\Traits;

use Avax\Database\Migration\Design\Column\Renderer\ColumnDSLRenderer;

/**
 * Trait TableRenderDslTrait
 *
 * Provides Domain-Specific Language (DSL) rendering capabilities for database table definitions.
 * Implement the Single Responsibility Principle by focusing solely on DSL generation logic.
 *
 * @package Avax\Database\Migration\Design\Table\Traits
 *
 * @since   1.0.0
 */
trait TableRenderDslTrait
{
    /**
     * Converts the table definition into a DSL representation.
     *
     * Transforms the internal column collection into a formatted DSL string using
     * the ColumnDSLRenderer. Follows the Command Query Separation principle by
     * performing a pure transformation operation.
     *
     * @return string The generated DSL representation of the table structure
     */
    public function toDsl() : string
    {
        // Initialize collection for DSL line storage
        $lines = [];

        // Transform each column definition into its DSL representation
        foreach ($this->getColumns() as $column) {
            // Delegate rendering responsibility to a specialized renderer
            $lines[] = (new ColumnDSLRenderer())->format(column: $column->getBuilder());
        }

        // Join DSL lines with proper indentation
        return implode(
            separator: PHP_EOL . '            ',
            array    : $lines
        );
    }
}