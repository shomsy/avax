<?php

declare(strict_types=1);

/**
 * Domain-Specific Language (DSL) for Database Schema Design.
 *
 * This namespace encapsulates the column definition DSL components,
 * providing a fluent interface for database schema manipulation.
 */

namespace Avax\Database\Migration\Design\Column\DSL\FluentModifiers;

use Avax\Database\Migration\Design\Column\DSL\ColumnDefinition;
use Avax\Database\Migration\Design\Column\Enums\ColumnType;

/**
 * Domain-Specific Column Definition Defaults Trait.
 *
 * Provides a collection of standardized column definitions following Domain-Driven Design principles.
 * This trait encapsulates common database schema patterns, offering a semantic layer
 * above raw SQL definitions to express business domain concepts.
 *
 * @package Avax\Database\Migration\Design\Column\DSL\FluentModifiers
 * @since   1.0.0
 */
trait ColumnDSLDefaults
{
    /**
     * Defines a standardized auto-incrementing primary key identifier.
     *
     * Implements the Identity Field pattern using a BIGINT type to ensure
     * sufficient capacity for large datasets. This follows the ubiquitous
     * language principle of DDD by providing a clear, domain-focused identifier.
     *
     * @return ColumnDefinition A fluent interface for column configuration
     * @throws \ReflectionException When reflection fails during object instantiation
     */
    public function id() : ColumnDefinition
    {
        // Create a primary key column with auto-increment capability
        return ColumnDefinition::make(
            name: 'id',
            type: ColumnType::BIGINT
        )
            ->primary()
            ->autoIncrement();
    }

    /**
     * Establishes temporal tracking for entity lifecycle events.
     *
     * Implements the Audit Trail pattern through timestamp columns that automatically
     * track entity creation and modification times. This supports both auditing
     * requirements and temporal queries within the domain.
     *
     * @return array{ColumnDefinition, ColumnDefinition} An array containing created_at and updated_at columns
     * @throws \ReflectionException When reflection fails during object instantiation
     */
    public function timestamps() : array
    {
        // Define creation timestamp column
        $createdAt = ColumnDefinition::make(
            name: 'created_at',
            type: ColumnType::TIMESTAMP
        )->nullable();

        // Define update timestamp column
        $updatedAt = ColumnDefinition::make(
            name: 'updated_at',
            type: ColumnType::TIMESTAMP
        )->nullable();

        // Return both columns as a tuple
        return [
            $createdAt,
            $updatedAt,
        ];
    }

    /**
     * Implements the Soft Delete pattern for logical record deletion.
     *
     * Creates a nullable timestamp column that enables logical deletion without
     * a physical record removal, supporting data recovery and maintaining referential integrity.
     * This pattern is essential for maintaining audit trails and implementing undo operations.
     *
     * @return ColumnDefinition A fluent interface for column configuration
     * @throws \ReflectionException When reflection fails during object instantiation
     */
    public function softDeletes() : ColumnDefinition
    {
        // Create a nullable timestamp column for soft deletes
        return ColumnDefinition::make(
            name: 'deleted_at',
            type: ColumnType::TIMESTAMP
        )->nullable();
    }

    /**
     * Establishes a UUID-based primary key for distributed systems.
     *
     * Implements a distributed-friendly primary key strategy using UUIDs,
     * enabling reliable unique identification across distributed systems
     * without central coordination. This pattern supports horizontal scaling
     * and microservices architecture.
     *
     * @return ColumnDefinition A fluent interface for column configuration
     * @throws \ReflectionException When reflection fails during object instantiation
     */
    public function uuidPrimary() : ColumnDefinition
    {
        // Create a UUID-based primary key column
        return ColumnDefinition::make(
            name: 'id',
            type: ColumnType::UUID
        )->primary();
    }

    /**
     * @throws \ReflectionException
     */
    public function char(string $name, int $length = 255) : ColumnDefinition
    {
        return ColumnDefinition::make(name: $name, type: ColumnType::CHAR)
            ->length(length: $length);
    }

    /**
     * @throws \ReflectionException
     */
    public function tinyText(string $name) : ColumnDefinition
    {
        return ColumnDefinition::make(name: $name, type: ColumnType::TINYTEXT);
    }

    /**
     * @throws \ReflectionException
     */
    public function mediumText(string $name) : ColumnDefinition
    {
        return ColumnDefinition::make(name: $name, type: ColumnType::MEDIUMTEXT);
    }

    /**
     * @throws \ReflectionException
     */
    public function longText(string $name) : ColumnDefinition
    {
        return ColumnDefinition::make(name: $name, type: ColumnType::LONGTEXT);
    }

    /**
     * @throws \ReflectionException
     */
    public function tinyInteger(string $name) : ColumnDefinition
    {
        return ColumnDefinition::make(name: $name, type: ColumnType::TINYINT);
    }

    /**
     * @throws \ReflectionException
     */
    public function mediumInteger(string $name) : ColumnDefinition
    {
        return ColumnDefinition::make(name: $name, type: ColumnType::MEDIUMINT);
    }

    /**
     * @throws \ReflectionException
     */
    public function ipAddress(string $name = 'ip_address') : ColumnDefinition
    {
        return ColumnDefinition::make(name: $name, type: ColumnType::VARCHAR)
            ->length(length: 45);
    }

    /**
     * @throws \ReflectionException
     */
    public function macAddress(string $name = 'mac_address') : ColumnDefinition
    {
        return ColumnDefinition::make(name: $name, type: ColumnType::VARCHAR)
            ->length(length: 17);
    }

    /**
     * @throws \ReflectionException
     */
    public function vector(string $name, int $dimensions = 1536) : ColumnDefinition
    {
        return ColumnDefinition::make(name: $name, type: ColumnType::VECTOR)
            ->length(length: $dimensions);
    }

    /**
     * @throws \ReflectionException
     */
    public function geography(string $name) : ColumnDefinition
    {
        return ColumnDefinition::make(name: $name, type: ColumnType::GEOGRAPHY);
    }
}