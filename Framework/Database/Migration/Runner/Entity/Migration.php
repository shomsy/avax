<?php

declare(strict_types=1);

/**
 * Class Migration
 *
 * This class represents a Migration entity in the domain layer. It encapsulates:
 * - The name of the migration.
 * - The date and time when the migration was executed.
 *
 * Responsibilities:
 * - Store and provide access to migration-related data.
 * - Offer additional utilities for validation and serialization.
 *
 * Adheres to:
 * - Single Responsibility Principle (SRP): Only holds migration-specific data and logic.
 * - Immutability: The properties are set at construction and cannot be changed afterward.
 */

namespace Gemini\Database\Migration\Runner\Entity;

use DateTimeImmutable;
use Gemini\Database\QueryBuilder\QueryBuilder;
use InvalidArgumentException;
use JsonSerializable;

/**
 * The Migration class represents a database migration.
 * It stores the migration's name and the date/time of its execution.
 *
 * Features:
 * - Provides methods to retrieve migration details.
 * - Implements validation for robust handling of migration data.
 * - Supports JSON serialization for external APIs or storage.
 */
class Migration implements JsonSerializable
{
    /**
     * Constructor for the Migration class.
     *
     * @param string            $migrationName The name of the migration (must be non-empty).
     * @param DateTimeImmutable $executedAt    The date and time the migration was executed.
     *
     * @throws \InvalidArgumentException If the migration name is empty or invalid.
     */
    public function __construct(
        protected string            $migrationName,
        protected DateTimeImmutable $executedAt,
        protected QueryBuilder      $queryBuilder
    ) {
        $this->validateMigrationName(migrationName: $migrationName);
    }

    /**
     * Validates the migration name.
     *
     * @param string $migrationName The name of the migration.
     *
     * @throws \InvalidArgumentException If the migration name is empty or invalid.
     */
    private function validateMigrationName(string $migrationName) : void
    {
        if (trim($migrationName) === '') {
            throw new InvalidArgumentException(message: 'Migration name cannot be empty.');
        }

        if (strlen($migrationName) > 255) {
            throw new InvalidArgumentException(message: 'Migration name cannot exceed 255 characters.');
        }
    }

    /**
     * Creates a Migration instance from an array of data.
     *
     * @param array $data An associative array containing 'migration_name' and 'executed_at' keys.
     *
     * @return static A new Migration instance created from the provided data.
     * @throws \InvalidArgumentException If required, data is missing or invalid.
     */
    public static function fromArray(array $data) : self
    {
        if (! isset($data['migration_name'], $data['executed_at'])) {
            throw new InvalidArgumentException(message: 'Missing required keys: "migration_name" and "executed_at".');
        }

        $executedAt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $data['executed_at']);
        if ($executedAt === false) {
            throw new InvalidArgumentException(
                message: 'Invalid date format for "executed_at". Expected "Y-m-d H:i:s".'
            );
        }

//        return new self(
//            migrationName: $data['migration_name'],
//            executedAt   : $executedAt,
//            queryBuilder : $this->queryBuilder
//        );
    }

    /**
     * Alias for `getMigrationName`, used for compatibility with other systems.
     *
     * @return string The name of the migration.
     */
    public function getName() : string
    {
        return $this->getMigrationName();
    }

    /**
     * Retrieves the name of the migration.
     *
     * @return string The name of the migration.
     */
    public function getMigrationName() : string
    {
        return $this->migrationName;
    }

    /**
     * Gets the date and time when the migration was executed.
     *
     * @return DateTimeImmutable The datetime representing when the migration was executed.
     */
    public function getExecutedAt() : DateTimeImmutable
    {
        return $this->executedAt;
    }

    /**
     * Prepares the migration instance for JSON serialization.
     *
     * @return array The migration data ready for JSON encoding.
     */
    public function jsonSerialize() : array
    {
        return $this->toArray();
    }

    /**
     * Converts the migration instance to an associative array.
     *
     * @return array The migration data as an associative array.
     */
    public function toArray() : array
    {
        return [
            'migration_name' => $this->migrationName,
            'executed_at'    => $this->executedAt->format(format: 'Y-m-d H:i:s'),
        ];
    }
}
