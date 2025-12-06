<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Runner\Repository;

/**
 * Interface MigrationRepositoryInterface
 *
 * Defines a contract for managing database migrations.
 * This interface abstracts migration management to ensure consistent migration operations across different
 * implementations.
 */
interface MigrationRepositoryInterface
{
    /**
     * Registers a migration with specified details into the database.
     *
     * @param string $migration  The name or identifier of the migration.
     * @param string $executable The class- or identifier-responsible for executing the migration.
     * @param int    $batch      The batch number that groups this migration with others.
     *
     * The `save` method is crucial for keeping a record of applied migrations
     * along with their batch number to allow rollback or re-execution of specific batches.
     */
    public function save(string $migration, string $executable, int $batch) : void;

    /**
     * Removes a specific migration entry from the database.
     *
     * @param string $migration The name or identifier of the migration to be deleted.
     *
     * Use `delete` to remove the record of a migration, especially if it was applied
     * erroneously or if it needs to be reapplied from scratch.
     */
    public function delete(string $migration) : void;

    /**
     * Checks if a certain migration is recorded in the database.
     *
     * @param string $migration The name or identifier of the migration.
     *
     * @return bool Returns true if the migration exists, otherwise false.
     *
     * The `has` method helps to verify if a migration has already been applied
     * to avoid duplicate application of the same migration.
     */
    public function has(string $migration) : bool;

    /**
     * Retrieves all migration records that have been executed.
     *
     * @return array An array containing details of all executed migrations.
     *
     * The `getAll` method provides a comprehensive list of all migrations that
     * have been executed, useful for audits and tracking the history of migrations.
     */
    public function getAll() : array;

    /**
     * Fetches migrations belonging to a specific batch.
     *
     * @param int $batch The batch number to filter migrations by.
     *
     * @return array An array of migrations under the given batch.
     *
     * The `getMigrationsByBatch` method is useful for operations that need to
     * target specific groups of migrations, such as rolling back a single batch.
     */
    public function getMigrationsByBatch(int $batch) : array;

    /**
     * Gets the highest batch number currently in use.
     *
     * @return int The highest batch number.
     *
     * The `getLatestBatch` method is essential for determining the most recent
     * group of migrations that were applied, often used to target the latest set
     * of migrations for rollbacks.
     */
    public function getLatestBatch() : int;

    /**
     * Removes all migration records, effectively resetting the migration state.
     *
     * Using `dropAllMigrations` prepares the system for a fresh start of migrations,
     * useful in scenarios where the entire migration history needs to be cleared.
     */
    public function dropAllMigrations() : void;

    /**
     * Returns all unexpected (pending) migrations from the database.
     *
     * @return array<int, array<string, mixed>>
     */
    public function allPending() : array;

    /**
     * Deletes all migrations associated with a given batch ID.
     *
     * @param int $batch The batch number to delete.
     */
    public function removeByBatch(int $batch) : void;

    /**
     * Retrieves all migrations for a specific batch in reverse order.
     *
     * @param int $batch The batch to search.
     *
     * @return array<int, array<string, mixed>>
     */
    public function findByBatch(int $batch) : array;

}