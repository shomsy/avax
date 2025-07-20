<?php

declare(strict_types=1);

namespace Gemini\Database\Migration\Design\Column;

use Gemini\Database\Migration\Design\Column\DSL\ColumnDefinition;
use Gemini\Database\Migration\Design\Column\DSL\FluentModifiers\ColumnDSLDefaults;
use Gemini\Database\Migration\Design\Column\DTO\ColumnAttributesDTO;
use Gemini\Database\Migration\Design\Column\Enums\ColumnType;
use RuntimeException;

/**
 * Factory and macro layer for fluent column construction.
 *
 * Delegates schema building to ColumnDefinition while also exposing
 * DDD-safe shortcuts like `id()`, `timestamps()`, etc.
 *
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
final readonly class Column
{
    use ColumnDSLDefaults;

    /**
     * Creates a ColumnDefinition via a DSL method call
     *
     * This method dynamically interprets the method name (e.g., `string`, `decimal`)
     * and maps it to an enum `ColumnType`, while applying appropriate DSL parameters.
     *
     * DSL-idiomatic:
     * - $table->string('name', 255)
     * - $table->decimal('price', 10, 2)
     * - $table->enum('type', ['free', 'paid'])
     *
     * @param string            $method    The column type method (e.g., 'string', 'decimal')
     * @param array<int, mixed> $arguments DSL arguments passed (name, length/precision/etc.)
     *
     * @return ColumnDefinition Returns a fully constructed column schema node
     *
     * @throws RuntimeException If the first argument (column name) is missing or invalid
     * @throws \ReflectionException
     */
    public function create(string $method, array $arguments) : ColumnDefinition
    {
        /**
         * Destructure the DSL arguments for clarity and DSL alignment.
         *
         * - $name: Column name (required)
         * - $size: Size, length, precision (optional)
         * - $details: Enum options or scale (optional)
         */
        [$name, $size, $details] = array_pad($arguments, 3, null);

        // Defensive: Fail early if column name is not provided
        if (empty($name) || ! is_string($name)) {
            throw new RuntimeException(message: "Missing or invalid column name for method: '{$method}'");
        }

        // Normalize method to ColumnType Enum (via alias support)
        $type = ColumnType::map(input: $method);

        // Dynamically collect any additional DSL parameters
        $attributes = match ($type) {
            ColumnType::VARCHAR,
            ColumnType::CHAR  => ['length' => $size ?? 255],

            ColumnType::DECIMAL,
            ColumnType::DOUBLE,
            ColumnType::FLOAT => [
                'precision' => $size ?? 10,
                'scale'     => $details ?? 2,
            ],

            ColumnType::ENUM,
            ColumnType::SET   => ['enum' => is_array($size) ? $size : []],

            default           => []
        };

        // Wrap all attributes in a strict, validated DTO
        $dto = new ColumnAttributesDTO(
            data: array_merge(
                      ['name' => $name, 'type' => $type],
                      $attributes
                  )
        );

        // Create immutable column node using named constructor
        return ColumnDefinition::make(
            name: $dto->name,
            type: $dto->type
        );
    }

}