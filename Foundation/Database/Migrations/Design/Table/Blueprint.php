<?php

declare(strict_types=1);

namespace Avax\Migrations\Design\Table;

use Avax\Database\QueryBuilder\Core\Grammar\GrammarInterface;
use Avax\Migrations\Design\Column\DSL\ColumnDefinition;
use Avax\Migrations\Design\Column\Renderer\ColumnSQLRenderer;

/**
 * Enterprise-grade designer for defining database table structures.
 *
 * -- intent: provide a collection-based DSL for managing multiple column definitions.
 */
final class Blueprint
{
    // Storage for the column design objects
    private array $columns = [];

    // Storage for commands (drop, rename, etc.)
    private array $commands = [];

    // Whether the table is being created or altered
    private bool $creating = true;

    /**
     * Constructor promoting the target table name via PHP 8.3 features.
     *
     * -- intent: capture the identifier for the table being designed.
     *
     * @param string $table Technical table name
     */
    public function __construct(
        private readonly string $table
    ) {}

    /**
     * Mark the blueprint for table alteration instead of creation.
     */
    public function setAlterMode() : self
    {
        $this->creating = false;

        return $this;
    }

    /**
     * Add a high-performance auto-incrementing primary key ID.
     *
     * -- intent: provide a pragmatic shorthand for the standard 'id' column.
     *
     * @param string $name Technical name, defaults to 'id'
     *
     * @return ColumnDefinition
     */
    public function id(string $name = 'id') : ColumnDefinition
    {
        return $this->addColumn(type: 'BIGINT', name: $name)->autoIncrement();
    }

    /**
     * Internal technician for registering a new column design.
     *
     * -- intent: centralize column object instantiation and storage.
     *
     * @param string $type Technical database type
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    private function addColumn(string $type, string $name) : ColumnDefinition
    {
        $column          = new ColumnDefinition(name: $name, type: $type);
        $this->columns[] = $column;

        return $column;
    }

    // ========================================
    // NUMERIC TYPES
    // ========================================

    /**
     * Add a TINYINT column (1 byte, -128 to 127).
     *
     * -- intent: provide storage for very small integers.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function tinyInteger(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'TINYINT', name: $name);
    }

    /**
     * Add a SMALLINT column (2 bytes, -32,768 to 32,767).
     *
     * -- intent: provide storage for small integers.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function smallInteger(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'SMALLINT', name: $name);
    }

    /**
     * Add an INT column (4 bytes, -2B to 2B).
     *
     * -- intent: provide storage for standard integers.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function integer(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'INT', name: $name);
    }

    /**
     * Add a BIGINT column (8 bytes, -9Q to 9Q).
     *
     * -- intent: provide storage for very large integers.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function bigInteger(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'BIGINT', name: $name);
    }

    /**
     * Add a DECIMAL column with precision and scale.
     *
     * -- intent: provide exact numeric storage for financial data.
     *
     * @param string $name      Technical name
     * @param int    $precision Total number of digits
     * @param int    $scale     Number of decimal places
     *
     * @return ColumnDefinition
     */
    public function decimal(string $name, int $precision = 8, int $scale = 2) : ColumnDefinition
    {
        return $this->addColumn(type: "DECIMAL({$precision},{$scale})", name: $name);
    }

    /**
     * Add a FLOAT column (4 bytes, approximate).
     *
     * -- intent: provide storage for single-precision floating point.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function float(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'FLOAT', name: $name);
    }

    /**
     * Add a DOUBLE column (8 bytes, approximate).
     *
     * -- intent: provide storage for double-precision floating point.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function double(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'DOUBLE', name: $name);
    }

    /**
     * Add a BOOLEAN column (TINYINT(1)).
     *
     * -- intent: provide storage for true/false values.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function boolean(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'TINYINT(1)', name: $name);
    }

    /**
     * Add a MEDIUMINT column (3 bytes, MySQL).
     *
     * -- intent: provide storage for medium-range integers.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function mediumInteger(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'MEDIUMINT', name: $name);
    }

    /**
     * Add a SERIAL column (PostgreSQL auto-increment INT).
     *
     * -- intent: provide PostgreSQL-native auto-incrementing integer.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function serial(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'SERIAL', name: $name);
    }

    /**
     * Add a BIGSERIAL column (PostgreSQL auto-increment BIGINT).
     *
     * -- intent: provide PostgreSQL-native auto-incrementing big integer.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function bigSerial(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'BIGSERIAL', name: $name);
    }

    /**
     * Add a REAL column (4 bytes, approximate).
     *
     * -- intent: provide storage for real numbers (alias for FLOAT in some DBs).
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function real(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'REAL', name: $name);
    }

    // ========================================
    // STRING TYPES
    // ========================================

    /**
     * Add a variable-length string column.
     *
     * -- intent: provide a pragmatic shorthand for VARCHAR columns.
     *
     * @param string $name   Technical name
     * @param int    $length Maximum character capacity
     *
     * @return ColumnDefinition
     */
    public function string(string $name, int $length = 255) : ColumnDefinition
    {
        return $this->addColumn(type: "VARCHAR({$length})", name: $name);
    }

    /**
     * Add a fixed-length string column.
     *
     * -- intent: provide storage for fixed-width data like codes.
     *
     * @param string $name   Technical name
     * @param int    $length Exact character capacity
     *
     * @return ColumnDefinition
     */
    public function char(string $name, int $length = 255) : ColumnDefinition
    {
        return $this->addColumn(type: "CHAR({$length})", name: $name);
    }

    /**
     * Add a TEXT column (up to 64KB).
     *
     * -- intent: provide storage for medium-length text content.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function text(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'TEXT', name: $name);
    }

    /**
     * Add a MEDIUMTEXT column (up to 16MB).
     *
     * -- intent: provide storage for large text content.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function mediumText(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'MEDIUMTEXT', name: $name);
    }

    /**
     * Add a LONGTEXT column (up to 4GB).
     *
     * -- intent: provide storage for very large text content.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function longText(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'LONGTEXT', name: $name);
    }

    /**
     * Add a TINYTEXT column (up to 255 bytes, MySQL).
     *
     * -- intent: provide storage for very small text content.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function tinyText(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'TINYTEXT', name: $name);
    }

    /**
     * Add an NCHAR column (Unicode fixed-length string).
     *
     * -- intent: provide storage for Unicode fixed-width data.
     *
     * @param string $name   Technical name
     * @param int    $length Exact character capacity
     *
     * @return ColumnDefinition
     */
    public function nchar(string $name, int $length = 255) : ColumnDefinition
    {
        return $this->addColumn(type: "NCHAR({$length})", name: $name);
    }

    /**
     * Add an NVARCHAR column (Unicode variable-length string).
     *
     * -- intent: provide storage for Unicode variable-length text.
     *
     * @param string $name   Technical name
     * @param int    $length Maximum character capacity
     *
     * @return ColumnDefinition
     */
    public function nvarchar(string $name, int $length = 255) : ColumnDefinition
    {
        return $this->addColumn(type: "NVARCHAR({$length})", name: $name);
    }

    /**
     * Add an NTEXT column (Unicode large text).
     *
     * -- intent: provide storage for large Unicode text content.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function ntext(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'NTEXT', name: $name);
    }

    /**
     * Add a BINARY column for fixed-length binary data.
     *
     * -- intent: provide storage for binary data like hashes.
     *
     * @param string $name   Technical name
     * @param int    $length Byte capacity
     *
     * @return ColumnDefinition
     */
    public function binary(string $name, int $length = 255) : ColumnDefinition
    {
        return $this->addColumn(type: "BINARY({$length})", name: $name);
    }

    /**
     * Add a UUID column (CHAR(36)).
     *
     * -- intent: provide storage for universally unique identifiers.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function uuid(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'CHAR(36)', name: $name);
    }

    /**
     * Add a native UUID column (PostgreSQL).
     *
     * -- intent: provide PostgreSQL-native UUID storage.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function uuidNative(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'UUID', name: $name);
    }

    /**
     * Add a VARBINARY column (variable-length binary).
     *
     * -- intent: provide storage for variable-length binary data.
     *
     * @param string $name   Technical name
     * @param int    $length Maximum byte capacity
     *
     * @return ColumnDefinition
     */
    public function varbinary(string $name, int $length = 255) : ColumnDefinition
    {
        return $this->addColumn(type: "VARBINARY({$length})", name: $name);
    }

    /**
     * Add a BLOB column (Binary Large Object, up to 64KB).
     *
     * -- intent: provide storage for binary files and data.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function blob(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'BLOB', name: $name);
    }

    /**
     * Add a TINYBLOB column (up to 255 bytes, MySQL).
     *
     * -- intent: provide storage for very small binary data.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function tinyBlob(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'TINYBLOB', name: $name);
    }

    /**
     * Add a MEDIUMBLOB column (up to 16MB, MySQL).
     *
     * -- intent: provide storage for medium-sized binary data.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function mediumBlob(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'MEDIUMBLOB', name: $name);
    }

    /**
     * Add a LONGBLOB column (up to 4GB, MySQL).
     *
     * -- intent: provide storage for very large binary data.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function longBlob(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'LONGBLOB', name: $name);
    }

    /**
     * Add a BYTEA column (PostgreSQL binary data).
     *
     * -- intent: provide PostgreSQL-native binary storage.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function bytea(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'BYTEA', name: $name);
    }

    /**
     * Add a BIT column (bit field).
     *
     * -- intent: provide storage for bit flags.
     *
     * @param string $name   Technical name
     * @param int    $length Number of bits
     *
     * @return ColumnDefinition
     */
    public function bit(string $name, int $length = 1) : ColumnDefinition
    {
        return $this->addColumn(type: "BIT({$length})", name: $name);
    }

    // ========================================
    // DATE/TIME TYPES
    // ========================================

    /**
     * Add a DATE column (YYYY-MM-DD).
     *
     * -- intent: provide storage for calendar dates.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function date(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'DATE', name: $name);
    }

    /**
     * Add a DATETIME column (YYYY-MM-DD HH:MM:SS).
     *
     * -- intent: provide storage for precise timestamps.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function datetime(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'DATETIME', name: $name);
    }

    /**
     * Add a TIMESTAMP column (auto-updating).
     *
     * -- intent: provide storage for event timestamps.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function timestamp(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'TIMESTAMP', name: $name);
    }

    /**
     * Add a TIME column (HH:MM:SS).
     *
     * -- intent: provide storage for time of day.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function time(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'TIME', name: $name);
    }

    /**
     * Add a YEAR column (4-digit year).
     *
     * -- intent: provide storage for year values.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function year(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'YEAR', name: $name);
    }

    /**
     * Add an INTERVAL column (PostgreSQL time period).
     *
     * -- intent: provide storage for time intervals/durations.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function interval(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'INTERVAL', name: $name);
    }

    /**
     * Add basic created_at and updated_at timestamp columns.
     *
     * -- intent: provide a domain-specific shorthand for audit record-keeping.
     *
     * @return void
     */
    public function timestamps() : void
    {
        $this->addColumn(type: 'TIMESTAMP', name: 'created_at')->nullable();
        $this->addColumn(type: 'TIMESTAMP', name: 'updated_at')->nullable();
    }

    /**
     * Add a soft delete timestamp column.
     *
     * -- intent: provide support for soft deletion pattern.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function softDeletes(string $name = 'deleted_at') : ColumnDefinition
    {
        return $this->addColumn(type: 'TIMESTAMP', name: $name)->nullable();
    }

    // ========================================
    // SPECIAL TYPES
    // ========================================

    /**
     * Add a JSON column.
     *
     * -- intent: provide storage for structured JSON documents.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function json(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'JSON', name: $name);
    }

    /**
     * Add a JSONB column (PostgreSQL binary JSON).
     *
     * -- intent: provide optimized storage for JSON with indexing.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function jsonb(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'JSONB', name: $name);
    }

    /**
     * Add an ENUM column with allowed values.
     *
     * -- intent: provide storage for predefined value sets.
     *
     * @param string $name   Technical name
     * @param array  $values Allowed values
     *
     * @return ColumnDefinition
     */
    public function enum(string $name, array $values) : ColumnDefinition
    {
        $quoted = array_map(callback: fn ($v) => "'{$v}'", array: $values);

        return $this->addColumn(type: 'ENUM(' . implode(separator: ',', array: $quoted) . ')', name: $name);
    }

    /**
     * Add a SET column with multiple allowed values.
     *
     * -- intent: provide storage for multi-select value sets.
     *
     * @param string $name   Technical name
     * @param array  $values Allowed values
     *
     * @return ColumnDefinition
     */
    public function set(string $name, array $values) : ColumnDefinition
    {
        $quoted = array_map(callback: fn ($v) => "'{$v}'", array: $values);

        return $this->addColumn(type: 'SET(' . implode(separator: ',', array: $quoted) . ')', name: $name);
    }

    /**
     * Add an XML column.
     *
     * -- intent: provide storage for XML documents.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function xml(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'XML', name: $name);
    }

    // ========================================
    // GIS / SPATIAL TYPES
    // ========================================

    /**
     * Add a POINT column (geometric point).
     *
     * -- intent: provide storage for 2D point coordinates.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function point(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'POINT', name: $name);
    }

    /**
     * Add a LINESTRING column (geometric line).
     *
     * -- intent: provide storage for line/path coordinates.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function lineString(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'LINESTRING', name: $name);
    }

    /**
     * Add a POLYGON column (geometric polygon).
     *
     * -- intent: provide storage for area/boundary coordinates.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function polygon(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'POLYGON', name: $name);
    }

    /**
     * Add a GEOMETRY column (generic spatial type).
     *
     * -- intent: provide storage for any geometric shape.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function geometry(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'GEOMETRY', name: $name);
    }

    /**
     * Add a GEOGRAPHY column (geodetic coordinates).
     *
     * -- intent: provide storage for earth-surface coordinates.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function geography(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'GEOGRAPHY', name: $name);
    }

    // ========================================
    // POSTGRESQL SPECIFIC TYPES
    // ========================================

    /**
     * Add an INET column (PostgreSQL IP address).
     *
     * -- intent: provide storage for IPv4/IPv6 addresses.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function inet(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'INET', name: $name);
    }

    /**
     * Add a CIDR column (PostgreSQL network range).
     *
     * -- intent: provide storage for network CIDR blocks.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function cidr(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'CIDR', name: $name);
    }

    /**
     * Add a MACADDR column (PostgreSQL MAC address).
     *
     * -- intent: provide storage for hardware MAC addresses.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function macaddr(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'MACADDR', name: $name);
    }

    /**
     * Add a TSVECTOR column (PostgreSQL full-text search).
     *
     * -- intent: provide storage for indexed searchable text.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function tsvector(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'TSVECTOR', name: $name);
    }

    /**
     * Add a TSQUERY column (PostgreSQL full-text query).
     *
     * -- intent: provide storage for search queries.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function tsquery(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'TSQUERY', name: $name);
    }

    // ========================================
    // SQL SERVER SPECIFIC TYPES
    // ========================================

    /**
     * Add a MONEY column (SQL Server currency).
     *
     * -- intent: provide storage for monetary values.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function money(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'MONEY', name: $name);
    }

    /**
     * Add a SMALLMONEY column (SQL Server small currency).
     *
     * -- intent: provide storage for smaller monetary values.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function smallMoney(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'SMALLMONEY', name: $name);
    }

    /**
     * Add a UNIQUEIDENTIFIER column (SQL Server GUID).
     *
     * -- intent: provide storage for SQL Server GUIDs.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function uniqueIdentifier(string $name) : ColumnDefinition
    {
        return $this->addColumn(type: 'UNIQUEIDENTIFIER', name: $name);
    }

    /**
     * Add a ROWVERSION column (SQL Server row versioning).
     *
     * -- intent: provide automatic row version tracking.
     *
     * @param string $name Technical name
     *
     * @return ColumnDefinition
     */
    public function rowVersion(string $name = 'row_version') : ColumnDefinition
    {
        return $this->addColumn(type: 'ROWVERSION', name: $name);
    }

    /**
     * Drop a column from the table.
     */
    public function dropColumn(string ...$names) : void
    {
        foreach ($names as $name) {
            $this->commands[] = ['type' => 'drop', 'name' => $name];
        }
    }

    /**
     * Rename a column on the table.
     */
    public function renameColumn(string $from, string $to) : void
    {
        $this->commands[] = ['type' => 'rename', 'from' => $from, 'to' => $to];
    }

    /**
     * Translate the blueprint design into a collection of physical SQL statements.
     *
     * -- intent: coordinate the rendering of all designed columns into a CREATE or ALTER command.
     *
     * @param GrammarInterface $grammar The dialect technician for wrapping and syntax
     *
     * @return array<string> List of SQL statements to execute
     */
    public function toSql(GrammarInterface $grammar) : array
    {
        return $this->creating ? $this->toCreateSql(grammar: $grammar) : $this->toAlterSql(grammar: $grammar);
    }

    /**
     * Generate CREATE TABLE statement.
     */
    private function toCreateSql(GrammarInterface $grammar) : array
    {
        $renderer = new ColumnSQLRenderer();
        $columns  = array_map(
            callback: fn (ColumnDefinition $col) => $renderer->render(column: $col, grammar: $grammar),
            array   : $this->columns
        );

        $sql = "CREATE TABLE " . $grammar->wrap(value: $this->table) . " (";
        $sql .= implode(separator: ', ', array: $columns);
        $sql .= ")";

        return [$sql];
    }

    /**
     * Generate ALTER TABLE statements.
     */
    private function toAlterSql(GrammarInterface $grammar) : array
    {
        $sql      = [];
        $renderer = new ColumnSQLRenderer();

        // Handle new columns (ADD)
        foreach ($this->columns as $column) {
            $sql[] = "ALTER TABLE " . $grammar->wrap(value: $this->table) . " ADD " . $renderer->render(column: $column, grammar: $grammar);
        }

        // Handle commands (DROP, RENAME)
        foreach ($this->commands as $command) {
            if ($command['type'] === 'drop') {
                $sql[] = "ALTER TABLE " . $grammar->wrap(value: $this->table) . " DROP COLUMN " . $grammar->wrap(value: $command['name']);
            } elseif ($command['type'] === 'rename') {
                $sql[] = "ALTER TABLE " . $grammar->wrap(value: $this->table) . " RENAME COLUMN " . $grammar->wrap(value: $command['from']) . " TO " . $grammar->wrap(value: $command['to']);
            }
        }

        return $sql;
    }
}
