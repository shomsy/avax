<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Runner\Service;

use DateTimeImmutable;
use Avax\Database\Migration\Runner\Repository\MigrationRepositoryInterface;
use Avax\Database\QueryBuilder\QueryBuilder;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Class MigrationExecution
 *
 * Provides CRUD operations for database migrations with transactional safety and structured logging.
 * Uses a QueryBuilder abstraction to manage migration records efficiently.
 */
class MigrationExecution implements MigrationRepositoryInterface
{
    /**
     * The default table name used for storing migration data.
     */
    private const string TABLE_MIGRATIONS = 'migrations';

    /**
     * The default date and time format used for formatting and parsing dates.
     */
    private const string DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * Constructor to initialize the class with required dependencies.
     *
     * @param QueryBuilder         $queryBuilder The query builder instance for database interactions.
     * @param LoggerInterface|null $logger       Optional logger instance for logging purposes.
     *
     * @return void
     */
    public function __construct(
        private readonly QueryBuilder    $queryBuilder,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * Persists a new migration record into the database.
     *
     * @param string $migration  Name of the migration file or class.
     * @param string $executable Raw SQL or migration command executed.
     * @param int    $batch      Batch number that this migration belongs to.
     *
     * @throws InvalidArgumentException When the input is invalid.
     * @throws Throwable When the insert operation fails.
     */
    public function save(string $migration, string $executable, int $batch) : void
    {
        // Validate that both the migration name and executable are not empty.
        if (empty($migration) || empty($executable)) {
            throw new InvalidArgumentException(message: "Migration name and executable cannot be empty.");
        }

        // Validate that the batch ID is greater than 0.
        if ($batch < 1) {
            throw new InvalidArgumentException(message: "Batch ID must be greater than 0.");
        }

        // Generate the current timestamp in the defined DATE_FORMAT ('Y-m-d H:i:s').
        $timestamp = (new DateTimeImmutable())->format(format: self::DATE_FORMAT);

        // Save the migration record using a database transaction to ensure atomicity.
        $this->queryBuilder->transaction(operations: function () use ($migration, $executable, $batch, $timestamp) {
            // Specify the target database table and insert the migration data.
            $this->queryBuilder
                ->table(tableName: self::TABLE_MIGRATIONS) // Set the target table to 'migrations'.
                ->insert(
                    parameters: [
                                    'migration'   => $migration,    // Name of the migration file or class.
                                    'executable'  => $executable,  // The executed SQL or migration command.
                                    'batch'       => $batch,       // Grouping number for the migration (batch).
                                    'executed_at' => $timestamp,   // Timestamp of when the migration was saved.
                                ]
                )
                ->flush(); // Commit the database operation immediately.
        });

        // Log an informational message about the saved migration if a logger is available.
        $this->logger->info(message: "Saved migration '{$migration}' in batch {$batch}.");
    }

    /**
     * Checks whether a given migration exists in the database.
     *
     * @param string $migration Name of the migration to check.
     *
     * @return bool True if migration exists, false otherwise.
     *
     * @throws Throwable If the query fails.
     */
    public function has(string $migration) : bool
    {
        return $this->queryBuilder
            ->table(tableName: self::TABLE_MIGRATIONS)
            ->where(column: 'migration', value: $migration)
            ->exists();
    }

    /**
     * Retrieves all migration records.
     *
     * @return array List of all migrations in associative array format.
     *
     * @throws Throwable If retrieval fails.
     */
    public function getAll() : array
    {
        return $this->queryBuilder
            ->table(tableName: self::TABLE_MIGRATIONS)
            ->select('migration', 'executable', 'batch', 'executed_at')
            ->get()
            ->toArray();
    }

    /**
     * Retrieves all migrations associated with a specific batch.
     *
     * @param int $batch The batch number to filter migrations.
     *
     * @return array Migrations belonging to the given batch.
     *
     * @throws Throwable If retrieval fails.
     */
    public function getMigrationsByBatch(int $batch) : array
    {
        return $this->queryBuilder
            ->table(tableName: self::TABLE_MIGRATIONS)
            ->where(column: 'batch', value: $batch)
            ->get()
            ->toArray();
    }

    /**
     * Fetches the most recent batch number from the migrations table.
     *
     * @return int Latest batch number, or 0 if no migrations exist.
     *
     * @throws Throwable If the query fails.
     */
    public function getLatestBatch() : int
    {
        $result = $this->queryBuilder
            ->table(tableName: self::TABLE_MIGRATIONS)
            ->select(columns: 'MAX(batch) AS batch')
            ->get()
            ->first(key: 'batch');

        return $result !== null ? (int) $result : 0;
    }

    /**
     * Removes all migration records using the built-in truncate method.
     *
     * @throws Throwable If the truncate operation fails.
     */
    public function dropAllMigrations() : void
    {
        $this->queryBuilder->transaction(operations: function () {
            $this->queryBuilder
                ->table(tableName: self::TABLE_MIGRATIONS)
                ->truncate()
                ->flush();
        });

        $this->logger->info(message: "Dropped all migration records.");
    }

    /**
     * Returns all pending migrations (executed_at is NULL).
     *
     * @return array<int, array<string, mixed>>
     * @throws \Avax\Database\QueryBuilder\Exception\QueryBuilderException
     * @throws \JsonException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function allPending() : array
    {
        return $this->queryBuilder
            ->table(tableName: self::TABLE_MIGRATIONS)
            ->whereNull(column: 'executed_at')
            ->orderBy(column: 'id', direction: 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Deletes all migrations in the given batch.
     *
     * @param int $batch
     *
     * @throws \Exception
     */
    public function removeByBatch(int $batch) : void
    {
        $this->queryBuilder->transaction(operations: function () use ($batch) {
            $this->queryBuilder
                ->table(tableName: self::TABLE_MIGRATIONS)
                ->where(column: 'batch', value: $batch)
                ->delete()
                ->flush();
        });

        $this->logger->info(message: "🗑 Removed all migrations in batch {$batch}.");
    }

    /**
     * Removes a specific migration record from the database.
     *
     * @param string $migration Name of the migration to delete.
     *
     * @throws Throwable If deletion fails.
     */
    public function delete(string $migration) : void
    {
        $this->queryBuilder->transaction(operations: function () use ($migration) {
            $this->queryBuilder
                ->table(tableName: self::TABLE_MIGRATIONS)
                ->where(column: 'migration', value: $migration)
                ->delete()
                ->flush();
        });

        $this->logger->info(message: "Deleted migration '{$migration}'.");
    }

    /**
     * Finds all migrations for a specific batch in reverse order (for rollback).
     *
     * @param int $batch
     *
     * @return array<int, array<string, mixed>>
     * @throws \Avax\Database\QueryBuilder\Exception\QueryBuilderException
     * @throws \JsonException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Random\RandomException
     */
    public function findByBatch(int $batch) : array
    {
        return $this->queryBuilder
            ->table(tableName: self::TABLE_MIGRATIONS)
            ->where(column: 'batch', value: $batch)
            ->orderBy(column: 'id', direction: 'desc')
            ->get()
            ->toArray();
    }

}
