<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Design\Column\Builder;

use Avax\Database\Migration\Design\Column\DTO\ColumnAttributesDTO;
use Avax\Database\Migration\Design\Column\Enums\ColumnType;
use RuntimeException;

/**
 * Represents an immutable value object for column metadata in database migrations.
 *
 * This class encapsulates database column attributes in a strongly-typed, immutable
 * structure, providing a clean interface for accessing column properties. It serves
 * as a read-only facade over the underlying ColumnAttributesDTO data.
 *
 * @property string                     $name
 * @property ColumnType                 $type
 * @property int|null                   $length
 * @property int|null                   $precision
 * @property int|null                   $scale
 * @property bool|null                  $nullable
 * @property bool|null                  $unsigned
 * @property bool|null                  $autoIncrement
 * @property bool|null                  $primary
 * @property bool|null                  $unique
 * @property string|int|float|bool|null $default
 * @property array<string>|null         $enum
 * @property string|null                $generated
 * @property string|null                $after
 * @property bool|null                  $useCurrent
 * @property bool|null                  $useCurrentOnUpdate
 * @property string|null                $alias
 * @property string|null                $comment
 * @property array<string, mixed>       $foreign
 * @property array<string, mixed>       $columns
 *
 * @final     This class is not designed for inheritance
 * @immutable This class represents an immutable value object
 * @pattern   Value Object - Encapsulates column attributes in an immutable structure
 */
final class ColumnAttributes
{
    /**
     * Stores the internal attribute collection extracted from the DTO.
     *
     * The properties array maintains a key-value mapping of all column attributes,
     * providing O(1) access time for attribute lookups while maintaining
     * encapsulation of the underlying data structure.
     *
     * @var array<string, mixed> Key-value pairs of column attributes
     */
    private array $properties;

    /**
     * Constructs a new immutable ColumnAttributes instance.
     *
     * Uses constructor property promotion for concise initialization
     * while maintaining clean code principles through explicit type declarations
     * and validation at instantiation.
     *
     * @param ColumnAttributesDTO $dto Validated data transfer object containing column metadata
     */
    public function __construct(private readonly ColumnAttributesDTO $dto)
    {
        $fromDto          = $this->dto;
        $this->properties = get_object_vars($fromDto);
    }

    /**
     * Creates a minimal column definition with essential attributes.
     *
     * Factory method implementing the named parameters pattern for improved
     * readability and maintainability. Provides a convenient way to create
     * basic column definitions without full DTO instantiation.
     *
     * @param string     $name The logical identifier for the column
     * @param ColumnType $type The SQL data type specification
     *
     * @return self New instance with minimal column configuration
     * @throws \ReflectionException When DTO instantiation fails
     */
    public static function make(
        string     $name,
        ColumnType $type
    ) : self {
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
     * Provides dynamic access to column attributes via property syntax.
     *
     * Implements magic getter following clean code principles by providing
     * clear error messages and type-safe access to internal properties.
     *
     * @param string $name The attribute name to retrieve
     *
     * @return mixed The value of the requested attribute
     * @throws RuntimeException When accessing undefined attributes
     */
    public function __get(string $name) : mixed
    {
        if (! array_key_exists($name, $this->properties)) {
            throw new RuntimeException(
                message: sprintf('Column attribute "%s" does not exist.', $name)
            );
        }

        return $this->properties[$name];
    }

    /**
     * Supports PHP's isset() and empty() operations on attributes.
     *
     * Provides a clean interface for attribute existence checking while
     * maintaining encapsulation of internal property storage.
     *
     * @param string $name The attribute name to check
     *
     * @return bool True if the attribute exists, false otherwise
     */
    public function __isset(string $name) : bool
    {
        return array_key_exists($name, $this->properties);
    }

    /**
     * Explicitly verifies the existence of a column attribute.
     *
     * Provides a more semantic alternative to isset() for attribute checking,
     * following clean code principles with clear method naming.
     *
     * @param string $name The attribute name to verify
     *
     * @return bool True if the attribute exists, false otherwise
     */
    public function has(string $name) : bool
    {
        return array_key_exists($name, $this->properties);
    }

    /**
     * Retrieves an attribute value with optional default fallback.
     *
     * Implements null coalescing operator for safe attribute access,
     * following defensive programming practices.
     *
     * @param string     $name    The attribute name to retrieve
     * @param mixed|null $default The fallback value if attribute doesn't exist
     *
     * @return mixed The attribute value or default
     */
    public function get(string $name, mixed $default = null) : mixed
    {
        return $this->properties[$name] ?? $default;
    }

    /**
     * Exports all column attributes as an associative array.
     *
     * Provides a clean interface for serialization while maintaining
     * immutability of the internal property collection.
     *
     * @return array<string, mixed> Complete map of column attributes
     */
    public function toArray() : array
    {
        return $this->properties;
    }
}
