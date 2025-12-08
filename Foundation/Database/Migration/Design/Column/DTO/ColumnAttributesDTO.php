<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Design\Column\DTO;

use Avax\Database\Migration\Design\Column\Enums\ColumnType;
use Avax\DataHandling\ObjectHandling\DTO\AbstractDTO;
use Avax\DataHandling\Validation\Attributes\Rules\Enum;
use Avax\DataHandling\Validation\Attributes\Rules\Filled;

/**
 * Data Transfer Object representing database column attributes within the migration context.
 *
 * This immutable value object encapsulates the complete set of attributes that define
 * a database column's structure and behavior. It provides a type-safe way to transfer
 * column definitions between different layers of the application.
 *
 * @template-extends AbstractDTO<ColumnAttributesDTO>
 * @final
 */
final class ColumnAttributesDTO extends AbstractDTO
{
    /**
     * @var string The column identifier in the database schema
     */
    #[Filled]
    public string $name;

    /**
     * @var ColumnType The SQL data type of the column
     */
    #[Enum(ColumnType::class)]
    public ColumnType $type;

    /**
     * @var int|null The maximum length for string-based column types
     */
    public int|null $length = null;

    /**
     * @var int|null The total number of digits for numeric column types
     */
    public int|null $precision = null;

    /**
     * @var int|null The number of digits after the decimal point for numeric types
     */
    public int|null $scale = null;

    /**
     * @var bool|null Indicates if the column can contain NULL values
     */
    public bool|null $nullable = false;

    /**
     * @var bool|null Specifies if numeric column should be unsigned
     */
    public bool|null $unsigned = false;

    /**
     * @var bool|null Determines if column value should auto-increment
     */
    public bool|null $autoIncrement = false;

    /**
     * @var bool|null Indicates if column is part of primary key
     */
    public bool|null $primary = false;

    /**
     * @var bool|null Specifies if column values must be unique
     */
    public bool|null $unique = false;

    /**
     * @var string|int|float|bool|null Default value for the column
     */
    public string|int|float|bool|null $default = null;

    /**
     * @var array<string>|null Possible values for ENUM type columns
     */
    public array|null $enum = null;

    /**
     * @var string|null Expression for generated columns
     */
    public string|null $generated = null;

    /**
     * @var string|null Column name after which this column should be placed
     */
    public string|null $after = null;

    /**
     * @var bool|null Use current timestamp for temporal columns
     */
    public bool|null $useCurrent = false;

    /**
     * @var bool|null Update temporal columns on record modification
     */
    public bool|null $useCurrentOnUpdate = false;

    /**
     * @var string|null Alternative name for the column
     */
    public string|null $alias = null;

    /**
     * @var string|null Documentation or description for the column
     */
    public string|null $comment = null;

    /**
     * @var array<string, mixed> Foreign key relationship configuration
     */
    public array $foreign = [];

    /**
     * @var array<string, mixed> Nested column definitions for complex types
     */
    public array $columns = [];
}