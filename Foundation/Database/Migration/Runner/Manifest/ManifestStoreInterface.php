<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Runner\Manifest;

use Avax\Database\Migration\Runner\Manifest\DTO\CreateManifestEntryDTO;

/**
 * Defines the contract for managing migration manifest entries in the application.
 *
 * This service interface abstracts the business operations for migration manifest management,
 * providing a clean boundary between the domain logic and persistence layer. It follows
 * the Service Pattern from DDD to encapsulate complex migration tracking operations.
 *
 * @package Avax\Database\Migration\Runner\Manifest
 */
interface ManifestStoreInterface
{
    /**
     * Creates a new migration manifest entry in the store.
     *
     * Processes and validates the migration entry data through a DTO before persistence.
     * Ensures data integrity and consistent state transitions for new migrations.
     *
     * @param CreateManifestEntryDTO $dto Value object containing validated migration entry data
     *
     * @throws \SleekDB\Exceptions\IOException When a storage operation fails
     * @throws \SleekDB\Exceptions\InvalidArgumentException When entry data is invalid
     */
    public function createEntry(CreateManifestEntryDTO $dto) : void;

    /**
     * Retrieves all migration manifest entries from the store.
     *
     * Provides a complete view of the migration history for audit and management purposes.
     * Results are ordered by creation timestamp to maintain execution sequence.
     *
     * @return array<int, array<string, mixed>> Collection of all migration manifest entries
     *
     * @throws \SleekDB\Exceptions\IOException When retrieval operation fails
     */
    public function fetchAll() : array;

    /**
     * Retrieves all pending migrations that haven't been executed.
     *
     * Identifies migrations that need to be processed in the next migration run.
     * Filters entries based on execution status and ordering constraints.
     *
     * @return array<int, array<string, mixed>> Collection of pending migration entries
     *
     * @throws \SleekDB\Exceptions\IOException When the query operation fails
     */
    public function findPending() : array;

    /**
     * Performs rollback operations for migrations in a specific batch.
     *
     * Manages the state transition of migrations during a rollback process.
     * Updates manifest entries to reflect rollback status and timing.
     *
     * @param string $batch Identifier for the batch of migrations to rollback
     *
     * @throws \SleekDB\Exceptions\IOException When the rollback operation fails
     * @throws \SleekDB\Exceptions\InvalidArgumentException When batch identifier is invalid
     */
    public function rollbackBatch(string $batch) : void;

    /**
     * Locates a specific migration entry by its unique name.
     *
     * Provides direct access to individual migration metadata for verification
     * and state management purposes.
     *
     * @param string $migrationName Unique identifier/name of the migration
     *
     * @return array<string, mixed>|null Migration entry if found, null otherwise
     *
     * @throws \SleekDB\Exceptions\IOException When lookup operation fails
     */
    public function findByMigrationName(string $migrationName) : array|null;
}