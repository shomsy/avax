<?php

/**
 * Strict type declaration for enhanced type safety and better performance.
 */
declare(strict_types=1);

/**
 * Namespace declaration following PSR-4 autoloading standards.
 * Contains value objects related to SQL column type definitions in the migration context.
 */

namespace Avax\Database\Migration\Design\Column\Enums;

/**
 * Represents an immutable Value Object encapsulating SQL column type definition parameters.
 *
 * This class follows Domain-Driven Design principles by representing a concept from
 * the ubiquitous language of database schema design. It is marked as final to prevent
 * inheritance and ensure immutability through the readonly modifier.
 *
 * @package Avax\Database\Migration\Design\Column\Enums
 * @final
 * @readonly
 */
final readonly class SqlTypeDefinition
{
    /**
     * Constructs a new SQL type definition with its associated parameters.
     *
     * Uses constructor property promotion for concise and expressive initialization
     * of the value object's properties.
     *
     * @param string   $type      The SQL data type identifier (e.g., 'VARCHAR', 'DECIMAL')
     * @param int|null $length    Optional length parameter for types that support it (e.g., VARCHAR(255))
     * @param int|null $precision Optional precision for numeric types (total number of significant digits)
     * @param int|null $scale     Optional scale for numeric types (number of digits after decimal point)
     */
    public function __construct(
        public string   $type,
        public int|null $length = null,
        public int|null $precision = null,
        public int|null $scale = null,
    ) {}
}