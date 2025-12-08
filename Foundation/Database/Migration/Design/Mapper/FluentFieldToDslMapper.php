<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Design\Mapper;

use Avax\Database\Migration\Design\Column\Enums\ColumnType;
use Avax\Database\Migration\Design\Column\Enums\ReferentialAction;
use Avax\Database\Migration\Design\Table\Table;
use Avax\Database\Migration\Runner\DTO\FieldDTO;
use RuntimeException;
use Throwable;

/**
 * Maps database field specifications to fluent DSL expressions in the schema builder.
 *
 * This mapper translates abstract field definitions (FieldDTO) into concrete database schema
 * declarations using a fluent interface. It encapsulates the complexity of mapping various
 * field types, their modifiers, and foreign key relationships.
 *
 * @final   This class is not designed for extension as per Interface Segregation Principle
 *
 * @package Avax\Database\Migration\Design\Mapper
 * @version 1.0.0
 * @since   8.3
 */
final class FluentFieldToDslMapper implements FieldToDslMapperInterface
{
    /**
     * Transforms a field specification into its corresponding database schema representation.
     *
     * This method serves as the primary entry point for field-to-schema mapping operations.
     * It orchestrates the process of:
     * 1. Type resolution and column creation
     * 2. Foreign key relationship configuration (if applicable)
     * 3. Column modifier application
     *
     * @param Table    $table The schema builder table instance to modify
     * @param FieldDTO $field The field specification to transform
     *
     * @throws RuntimeException When column creation fails or invalid specifications are provided
     */
    public function apply(Table $table, FieldDTO $field) : void
    {
        try {
            // Map the field type to a concrete ColumnType enum
            $typeEnum = ColumnType::map(input: $field->type->value);

            // Create the base column definition
            $column = $this->createColumn(table: $table, typeEnum: $typeEnum, field: $field);

            // Apply foreign key specific configurations if applicable
            if ($typeEnum === ColumnType::FOREIGN_KEY) {
                $column = $this->applyForeignKeyActions(column: $column, field: $field);
            }

            // Apply any additional modifiers to the column
            $this->applyModifiers(
                table     : $table,
                column    : $column,
                modifiers : $field->attributes ?? [],
                typeMethod: strtolower($typeEnum->name)
            );
        } catch (Throwable $e) {
            throw new RuntimeException(
                message : sprintf("Column creation failed for method '%s': %s", $field->type->value, $e->getMessage()),
                previous: $e
            );
        }
    }

    /**
     * Creates a column instance based on the specified type and field configuration.
     *
     * @param Table      $table    The table blueprint instance
     * @param ColumnType $typeEnum The enumerated column type
     * @param FieldDTO   $field    The field specification
     *
     * @return object The created column instance
     */
    private function createColumn(Table $table, ColumnType $typeEnum, FieldDTO $field) : object
    {
        $args = $this->resolveColumnArguments(typeEnum: $typeEnum, field: $field);

        return $typeEnum === ColumnType::FOREIGN_KEY
            ? $table->foreignKey(...$args)
            : $table->{strtolower($typeEnum->name)}(...$args);
    }

    /**
     * Resolves constructor arguments for column creation based on type and field specification.
     *
     * Maps different column types to their required constructor arguments, handling default values
     * and mandatory parameters for each type.
     *
     * @param ColumnType $typeEnum The type of column being created
     * @param FieldDTO   $field    The field specification containing the parameters
     *
     * @return array<int, mixed> Resolved constructor arguments
     *
     * @throws RuntimeException When required foreign key parameters are missing
     * @noinspection PhpFeatureEnvyLocalInspection
     */
    private function resolveColumnArguments(ColumnType $typeEnum, FieldDTO $field) : array
    {
        return match ($typeEnum) {
            ColumnType::VARCHAR, ColumnType::CHAR                      => [
                $field->name,
                $field->length ?? 255,
            ],
            ColumnType::DECIMAL, ColumnType::FLOAT, ColumnType::DOUBLE => [
                $field->name,
                ...$this->hasPrecisionScale($field)
                    ? [$field->total, $field->places]
                    : throw new RuntimeException(
                        sprintf(
                            "Invalid precision/scale for '%s': total=%s, places=%s",
                            $field->name,
                            var_export($field->total, true),
                            var_export($field->places, true)
                        )
                    ),
            ],

            ColumnType::ENUM, ColumnType::SET                          => [
                $field->name,
                $field->enum ?? [],
            ],
            ColumnType::FOREIGN_KEY                                    => [
                $field->columns[0] ?? throw new RuntimeException(message: "Missing local column name for foreign key."),
                $field->references ?? throw new RuntimeException(message: "Missing 'references' for foreign key."),
                $field->on ?? throw new RuntimeException(message: "Missing 'on' (referenced table) for foreign key."),
            ],
            default                                                    => [$field->name],
        };
    }

    /**
     * Validates numeric precision and scale parameters for decimal-type columns.
     *
     * Ensures that the precision (total digits) and scale (decimal places) are valid
     * and logically consistent.
     *
     * @param FieldDTO $field The field specification to validate
     *
     * @return bool True if the precision/scale combination is valid
     */
    private function hasPrecisionScale(FieldDTO $field) : bool
    {
        return is_int($field->total)
               && is_int($field->places)
               && $field->total >= $field->places;
    }

    /**
     * Configures referential actions for foreign key constraints.
     *
     * @param object   $column The foreign key column instance
     * @param FieldDTO $field  The field specification containing referential actions
     *
     * @return object The modified column instance
     */
    private function applyForeignKeyActions(object $column, FieldDTO $field) : object
    {
        if (is_string($field->onDelete)) {
            $column = $column->onDelete(
                ReferentialAction::tryFrom(value: strtoupper(trim($field->onDelete)))
            );
        }

        if (is_string($field->onUpdate)) {
            $column = $column->onUpdate(
                ReferentialAction::tryFrom(value: strtoupper(trim($field->onUpdate)))
            );
        }

        return $column;
    }

    /**
     * Applies a sequence of modifiers to a column definition.
     *
     * Validates and applies each modifier in sequence, ensuring the modifier exists
     * for the given column type.
     *
     * @param Table  $table      The table blueprint instance
     * @param object $column     The column instance to modify
     * @param array  $modifiers  List of modifiers to apply
     * @param string $typeMethod The column type method name
     *
     * @throws RuntimeException When an invalid modifier is specified
     */
    private function applyModifiers(Table $table, object $column, array $modifiers, string $typeMethod) : void
    {
        foreach ($modifiers as $modifier) {
            if (! method_exists($column, $modifier)) {
                throw new RuntimeException(
                    message: sprintf(
                                 "Column modifier '%s' is not available on column type '%s'.",
                                 $modifier,
                                 $typeMethod
                             )
                );
            }
            $column = $column->{$modifier}();
            $table->replaceColumn(column: $column);
        }
    }
}