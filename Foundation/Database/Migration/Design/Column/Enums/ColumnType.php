<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Design\Column\Enums;

/**
 * Enum representing standardized SQL column types.
 *
 * Provides type-safe mapping to SQL-compatible type strings.
 * Improves reliability and prevents typos in column definitions.
 */
enum ColumnType: string
{
    /**
     * Uses the SupportsCaseMappedEnum trait to enable case-insensitive enum value mapping with alias support.
     *
     * This trait provides functionality for mapping string inputs to enum cases in a case-insensitive manner,
     * with support for custom aliases. It's particularly useful in Domain-Driven Design (DDD) when working
     * with value objects and enums that need flexible input handling.
     *
     * @template-implements SupportsCaseMappedEnum<static>
     *
     * @see   BackedEnum For backed enum compatibility
     * @since 8.3.0
     */
    use SupportsCaseMappedEnum;

    /**
     * Standard 4-byte integer type supporting values from -2^31 to 2^31-1
     */
    case INT = 'INT';

    /**
     * Represents an INTEGER column type in the database schema.
     *
     * This type is used for storing whole numbers without decimal points.
     * Typically used for primary keys, foreign keys, and numerical data that
     * doesn't require decimal precision.
     *
     * @var string
     * @since 1.0.0
     * @immutable
     * @see   \Avax\Database\Migration\Design\Column\Enums\ColumnType::isNumeric()
     * @see   \Avax\Database\Migration\Design\Column\Enums\ColumnType::requiresLength()
     */
    case INTEGER = 'INTEGER';

    /**
     * Large 8-byte integer type supporting values from -2^63 to 2^63-1
     */
    case BIGINT = 'BIGINT';

    /**
     * Small 2-byte integer type supporting values from -32,768 to 32,767
     */
    case SMALLINT = 'SMALLINT';

    /**
     * Medium 3-byte integer type supporting values from -8,388,608 to 8,388,607
     */
    case MEDIUMINT = 'MEDIUMINT';

    /**
     * Tiny 1-byte integer type supporting values from -128 to 127
     */
    case TINYINT = 'TINYINT';

    /**
     * Fixed-point decimal number with configurable precision and scale
     */
    case DECIMAL = 'DECIMAL';

    /**
     * Single-precision floating-point number (4 bytes)
     */
    case FLOAT = 'FLOAT';

    /**
     * Double-precision floating-point number (8 bytes)
     */
    case DOUBLE = 'DOUBLE';

    /**
     * Variable-length string type with a maximum length specification
     */
    case VARCHAR = 'VARCHAR';

    /**
     * Fixed-length string type, padded with spaces to specified length
     */
    case CHAR = 'CHAR';

    /**
     * Variable-length text type with a maximum size of 65,535 bytes
     */
    case TEXT = 'TEXT';

    /**
     * Variable-length text type with maximum size of 4GB
     */
    case LONGTEXT = 'LONGTEXT';

    /**
     * Variable-length text type with maximum size of 16MB
     */
    case MEDIUMTEXT = 'MEDIUMTEXT';

    /**
     * Variable-length text type with maximum size of 255 bytes
     */
    case TINYTEXT = 'TINYTEXT';

    /**
     * Date type storing year, month, and day
     */
    case DATE = 'DATE';

    /**
     * Date and time type with microsecond precision
     */
    case DATETIME = 'DATETIME';

    /**
     * Timestamp type for tracking record modifications
     */
    case TIMESTAMP = 'TIMESTAMP';

    /**
     * Time type storing hours, minutes, seconds
     */
    case TIME = 'TIME';

    /**
     * Year type storing values from 1901 to 2155
     */
    case YEAR = 'YEAR';

    /**
     * JSON document type with validation and indexing capabilities
     */
    case JSON = 'JSON';

    /**
     * Binary JSON type optimized for indexing and querying
     */
    case JSONB = 'JSONB';

    /**
     * Binary large object type for storing binary data
     */
    case BLOB = 'BLOB';

    /**
     * Enumerated type with predefined set of valid values
     */
    case ENUM = 'ENUM';

    /**
     * Set type allowing multiple values from predefined options
     */
    case SET = 'SET';

    /**
     * Vector type for AI/ML applications and similarity searches
     */
    case VECTOR = 'VECTOR';

    /**
     * Geographic spatial data type for location-based features
     */
    case GEOGRAPHY = 'GEOGRAPHY';

    /**
     * UUID type for globally unique identifiers (36 chars)
     */
    case UUID = 'UUID';

    /**
     * ULID type for sortable unique identifiers (26 chars)
     */
    case ULID = 'ULID';

    /**
     * Boolean type typically implemented as TINYINT(1)
     */
    case BOOLEAN = 'BOOLEAN';

    /**
     * Foreign key constraint type for referential integrity
     */
    case FOREIGN_KEY = 'FOREIGN KEY';

    /**
     * Standard index type for query optimization
     */
    case INDEX = 'INDEX';

    /**
     * Unique constraint index type ensuring value uniqueness
     */
    case UNIQUE = 'UNIQUE';

    /**
     * Full-text search index type for text search optimization
     */
    case FULLTEXT = 'FULLTEXT';

    /**
     * Spatial index type for geographic data queries
     */
    case SPATIAL = 'SPATIAL';

    /**
     * Resolves a ColumnType enum from a DSL method name.
     *
     * @param string $method The DSL method name (e.g., 'string', 'text', 'uuid')
     *
     * @return self The corresponding ColumnType enum
     *
     * @throws \InvalidArgumentException If the method is not recognized
     */
    public static function fromDslMethod(string $method) : self
    {
        return self::map(input: $method);
    }


    /**
     * Returns a mapping of DSL (Domain-Specific Language) column type aliases to their corresponding database types.
     * This method establishes a unified type system across the domain model and persistence layer.
     *
     * @return array<string, string> Associative array mapping DSL type aliases to concrete database column types
     */
    public static function dslAliases() : array
    {
        // String-based column type mappings
        return [
            // Standard string variations for flexible text storage
            'string'     => self::VARCHAR,    // Variable-length character string, default choice for text
            'varchar'    => self::VARCHAR,    // Alternative notation for VARCHAR type
            'char'       => self::CHAR,       // Fixed-length character string

            // Text storage variations with different capacity limits
            'text'       => self::TEXT,       // Standard text type for larger string storage
            'longText'   => self::LONGTEXT,   // Maximum capacity text storage
            'mediumText' => self::MEDIUMTEXT, // Medium capacity text storage
            'tinyText'   => self::TINYTEXT,   // Minimal capacity text storage

            // Unique identifier types
            'uuid'       => self::UUID,       // Universally Unique Identifier (128-bit)
            'ulid'       => self::ULID,       // Universally Unique Lexicographically Sortable Identifier

            // JSON data types
            'json'       => self::JSON,       // Standard JSON storage type
            'jsonb'      => self::JSONB,      // Binary JSON storage (PostgreSQL specific)

            // Integer-based numeric types
            'int'        => self::INTEGER,    // Standard integer type
            'integer'    => self::INTEGER,    // Alternative notation for INTEGER
            'bigint'     => self::BIGINT,     // Large-range integer type
            'smallint'   => self::SMALLINT,   // Small-range integer type
            'tinyint'    => self::TINYINT,    // Minimal-range integer type

            // Decimal number types
            'decimal'    => self::DECIMAL,    // Exact decimal number type
            'float'      => self::FLOAT,      // Floating-point number type
            'double'     => self::DOUBLE,     // Double precision floating-point type

            // Boolean type aliases
            'boolean'    => self::BOOLEAN,    // Standard boolean type
            'bool'       => self::BOOLEAN,    // Alternative notation for BOOLEAN

            // Date and time types
            'date'       => self::DATE,       // Date storage without time
            'datetime'   => self::DATETIME,   // Combined date and time storage
            'timestamp'  => self::TIMESTAMP,  // Timestamp with timezone awareness
            'time'       => self::TIME,       // Time storage without date
            'year'       => self::YEAR,       // Year storage only

            'foreign'     => self::FOREIGN_KEY,
            'foreignKey'  => self::FOREIGN_KEY,
            'foreign_key' => self::FOREIGN_KEY,
            'foreign key' => self::FOREIGN_KEY,
        ];
    }

    /**
     * Returns only the raw SQL type string for this column, without metadata.
     *
     * Useful for quick compatibility checks, logging, or fallback rendering.
     *
     * Delegates to toSqlTypeDefinition() and extracts the base SQL type.
     *
     * @return string SQL-compatible column type name (e.g., "CHAR", "TINYINT")
     */
    public function toSqlType() : string
    {
        return $this->toSqlTypeDefinition()->type;
    }

    /**
     * Returns the physical SQL type representation for the current ColumnType,
     * including fixed-length or precision information when relevant.
     *
     * This is critical for type-safe schema generation (e.g. CHAR(36) for UUID).
     *
     * @return SqlTypeDefinition Full SQL type contract with constraints
     */
    public function toSqlTypeDefinition() : SqlTypeDefinition
    {
        return match ($this) {
            self::UUID    => new SqlTypeDefinition(type: 'CHAR', length: 36),
            self::ULID    => new SqlTypeDefinition(type: 'CHAR', length: 26),
            self::BOOLEAN => new SqlTypeDefinition(type: 'TINYINT', length: 1),
            self::VARCHAR => new SqlTypeDefinition(type: 'VARCHAR', length: 255),
            self::CHAR    => new SqlTypeDefinition(type: 'CHAR', length: 255),
            self::DECIMAL => new SqlTypeDefinition(type: 'DECIMAL', precision: 10, scale: 2),
            self::FLOAT,
            self::DOUBLE  => new SqlTypeDefinition(type: $this->value, precision: 10, scale: 2),
            default       => new SqlTypeDefinition(type: $this->value),
        };
    }

    /**
     * Determines whether the current column type represents an index type.
     *
     * @return bool True if the type is an index type, false otherwise
     */
    public function isIndex() : bool
    {
        return match ($this) {
            self::INDEX,
            self::UNIQUE,
            self::FULLTEXT,
            self::SPATIAL => true,
            default       => false,
        };
    }

    /**
     * Determines whether the column type requires a length specification.
     *
     * @return bool True if length is required, false otherwise
     */
    public function requiresLength() : bool
    {
        return match ($this) {
            self::VARCHAR,
            self::CHAR,
            self::UUID,
            self::ULID,
            self::VECTOR => true,
            default      => false,
        };
    }

    /**
     * Determines if the column type supports precision and scale parameters.
     *
     * @return bool True if precision/scale are supported, false otherwise
     */
    public function supportsPrecision() : bool
    {
        return match ($this) {
            self::DECIMAL,
            self::FLOAT,
            self::DOUBLE => true,
            default      => false,
        };
    }

    /**
     * Indicates whether the column type is temporal (timestamp/datetime/etc).
     *
     * @return bool
     */
    public function isTemporal() : bool
    {
        return match ($this) {
            self::TIMESTAMP,
            self::DATETIME,
            self::DATE,
            self::TIME,
            self::YEAR => true,
            default    => false,
        };
    }

    /**
     * Determines if the type is string-compatible.
     *
     * @return bool
     */
    public function isString() : bool
    {
        return match ($this) {
            self::VARCHAR,
            self::CHAR,
            self::TEXT,
            self::LONGTEXT,
            self::MEDIUMTEXT,
            self::TINYTEXT,
            self::UUID,
            self::ULID => true,
            default    => false,
        };
    }

    /**
     * Returns the default length for types that require it.
     *
     * @return int|null Default length or null
     */
    public function defaultLength() : int|null
    {
        return match ($this) {
            self::UUID    => 36,
            self::ULID    => 26,
            self::CHAR,
            self::VARCHAR => 255,
            self::VECTOR  => 1536,
            default       => null,
        };
    }

    /**
     * Determines if the type is numeric.
     *
     * @return bool True if numeric
     */
    public function isNumeric() : bool
    {
        return match ($this) {
            self::INT,
            self::BIGINT,
            self::SMALLINT,
            self::MEDIUMINT,
            self::TINYINT,
            self::DECIMAL,
            self::FLOAT,
            self::DOUBLE => true,
            default      => false,
        };
    }

    /**
     * Retrieves the primary DSL method alias for the current schema type.
     *
     * This method returns the first (most preferred) method name from the available
     * DSL method aliases. It's particularly useful in fluent schema definitions
     * where a consistent primary method name is required.
     *
     * @return string The primary DSL method alias for the current schema type
     * @see   preferredDslMethods() For the complete list of available DSL method aliases
     * @since 8.3
     */
    public function getPreferredAlias() : string
    {
        // Retrieve the first (primary) DSL method alias from the available methods
        return $this->preferredDslMethods()[0];
    }

    /**
     * Reverse map to prefer DSL-friendly names (e.g. use `string()` instead of `varchar()`).
     * Returns an array of preferred DSL (Domain Specific Language) method names for the current field type.
     *
     * This method maps enumeration cases to their corresponding fluent schema builder methods,
     * facilitating a more expressive and domain-driven database schema definition.
     *
     * @return array<int, string> Array of method names available for this field type
     * @throws never
     * @api
     * @since 1.0.0
     */
    public function preferredDslMethods() : array
    {
        // Match expression provides exhaustive type mapping for schema builder methods
        return match ($this) {
            // Maps VARCHAR type to both 'string' and 'varchar' method names for flexibility
            self::VARCHAR     => ['string', 'varchar'],

            // Boolean type supports both full and short method names
            self::BOOLEAN     => ['boolean', 'bool'],

            // Integer type supports both full and short method names
            self::INTEGER     => ['integer', 'int'],

            // Text type maps to the 'text' schema builder method
            self::TEXT        => ['text'],

            // LongText type maps to the camelCase 'longText' method
            self::LONGTEXT    => ['longText'],

            // Char type maps directly to the 'char' method
            self::CHAR        => ['char'],

            // Enum type maps to the 'enum' schema builder method
            self::ENUM        => ['enum'],

            self::FOREIGN_KEY => ['foreign', 'foreignKey', 'foreign_key'],


            // Fallback for any undefined types, converts enum case name to lowercase
            default           => [strtolower($this->name)],
        };
    }
}
