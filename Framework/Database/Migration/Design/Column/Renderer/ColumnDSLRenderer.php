<?php

declare(strict_types=1);

namespace Gemini\Database\Migration\Design\Column\Renderer;

use Gemini\Database\Migration\Design\Column\Builder\ColumnAttributes;

/**
 * Renders column definitions into Gemini migration DSL format.
 *
 * This final class is responsible for converting column attributes into
 * syntactically correct Laravel migration method chains. It follows DDD principles
 * by encapsulating all column DSL rendering logic in a dedicated service.
 *
 * @final
 */
final class ColumnDSLRenderer
{
    /**
     * Formats column attributes into a Laravel migration DSL string.
     *
     * This method transforms the domain model (ColumnAttributes) into a valid
     * Laravel migration method chain, handling type-specific formatting,
     * modifiers, and defaults according to Laravel's schema builder syntax.
     *
     * @param ColumnAttributes $column The column attributes to format
     *
     * @return string The formatted Laravel migration DSL statement
     */
    public function format(ColumnAttributes $column) : string
    {
        // Retrieves the preferred Domain-Specific Language (DSL) method mapping for the column type.
        $type = $column->type->getPreferredAlias();

        // Prepare base arguments starting with the column name
        $args = [$column->name];

        // Add length parameter for string-based column types
        if ($column->type->requiresLength() && $column->length !== null) {
            $args[] = $column->length;
        }

        // Add precision and scale for numeric types
        if ($column->type->supportsPrecision()) {
            $args[] = $column->precision ?? 10; // Default precision if isn't specified
            $args[] = $column->scale ?? 2;      // Default scale if isn't specified
        }

        // Construct the base column definition
        $dsl = sprintf(
            '$table->%s(%s)',
            $type,
            implode(', ', array_map(static fn($a) => var_export($a, true), $args))
        );

        // Define available column modifiers with their corresponding method names
        $modifiers = [
            'nullable'           => 'nullable',
            'unsigned'           => 'unsigned',
            'autoIncrement'      => 'autoIncrement',
            'primary'            => 'primary',
            'unique'             => 'unique',
            'useCurrent'         => 'useCurrent',
            'useCurrentOnUpdate' => 'useCurrentOnUpdate',
        ];

        // Apply modifiers if their corresponding attributes are true
        foreach ($modifiers as $attr => $method) {
            if ($column->{$attr} === true) {
                $dsl .= "->{$method}()";
            }
        }

        // Add default value if specified
        if ($column->default !== null) {
            $escaped = is_string($column->default) ? "'{$column->default}'" : var_export($column->default, true);
            $dsl     .= "->default({$escaped})";
        }

        // Terminate the statement with semicolon
        return $dsl . ';';
    }
}