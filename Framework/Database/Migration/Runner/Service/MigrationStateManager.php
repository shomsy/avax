<?php

declare(strict_types=1);

namespace Gemini\Database\Migration\Runner\Service;

use Gemini\Database\Migration\Runner\Exception\MigrationException;
use Gemini\Database\Migration\Runner\Migration;
use Gemini\Database\Migration\Runner\Repository\MigrationRepositoryInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Manages the lifecycle of migrations, including execution, rollback, refresh, and fresh operations.
 */
final readonly class MigrationStateManager
{
    public function __construct(
        private MigrationRepositoryInterface $migrationRepository,
        private LoggerInterface|null         $logger = null
    ) {}

    /**
     * Refresh migrations by rolling back all and reapplying them.
     *
     * @param array $availableMigrations List of available migration classes.
     *
     * @throws MigrationException If the refresh process fails.
     */
    public function refresh(array $availableMigrations) : void
    {
        try {
            $this->rollbackAll();
            $this->migrate(availableMigrations: $availableMigrations);
        } catch (Throwable $throwable) {
            throw new MigrationException(
                message : "Failed to refresh migrations: " . $throwable->getMessage(),
                previous: $throwable
            );
        }
    }

    /**
     * Rollback all migrations batch by batch.
     *
     * @throws MigrationException If rolling back migrations fails.
     */
    public function rollbackAll() : void
    {
        try {
            while ($batch = $this->migrationRepository->getLatestBatch()) {
                $this->rollbackBatch(batch: $batch);
            }
        } catch (Throwable $throwable) {
            throw new MigrationException(
                message : 'Failed to rollback all migrations: ' . $throwable->getMessage(),
                previous: $throwable
            );
        }
    }

    /**
     * Rollback a specific batch of migrations.
     *
     * @param int $batch Batch ID to rollback.
     *
     * @throws MigrationException If rolling back the batch fails.
     */
    public function rollbackBatch(int $batch) : void
    {
        try {
            foreach ($this->migrationRepository->getMigrationsByBatch(batch: $batch) as $migration) {
                $this->rollbackMigration(migration: $migration['migration']);
                $this->migrationRepository->delete(migration: $migration['migration']);
            }
        } catch (Throwable $throwable) {
            throw new MigrationException(
                message : "Failed to rollback batch " . $batch . ": " . $throwable->getMessage(),
                previous: $throwable
            );
        }
    }

    /**
     * Rollback a single migration.
     *
     * @param string $migration The migration class name.
     *
     * @throws MigrationException If rolling back the migration fails.
     */
    private function rollbackMigration(string $migration) : void
    {
        try {
            $fullNamespace = $this->resolveFullNamespace($migration);

            if (! class_exists($fullNamespace)) {
                throw new MigrationException(message: sprintf("Migration class '%s' not found.", $fullNamespace));
            }

            $instance = app($fullNamespace);
            if (! $instance instanceof Migration) {
                throw new MigrationException(
                    message: sprintf("Migration '%s' must extend the base Migration class.", $fullNamespace)
                );
            }

            $instance->executeDown();
        } catch (Throwable $throwable) {
            throw new MigrationException(
                message : sprintf("Failed to rollback migration '%s': %s", $migration, $throwable->getMessage()),
                previous: $throwable
            );
        }
    }

    /**
     * Resolves the full namespace of the migration class.
     *
     * @param string $className The migration class name.
     *
     * @return string Fully qualified namespace of the class.
     */
    private function resolveFullNamespace(string $className) : string
    {
        $availableNamespaces = config(key: 'app.namespaces.Migrations', default: []);

        $fullNamespace = rtrim((string) $availableNamespaces, '\\') . '\\' . ltrim($className, '\\');
        if (class_exists($fullNamespace)) {
            $this->logInfo('Resolved migration namespace: ' . $fullNamespace);

            return $fullNamespace;
        }

        throw new MigrationException(message: sprintf("Unable to resolve namespace for class: '%s'.", $className));
    }

    /**
     * Log an informational message.
     *
     * @param string $message The message to log.
     */
    private function logInfo(string $message) : void
    {
        $this->logger?->info(message: $message);
    }

    /**
     * Migrate all pending migrations.
     *
     * @param array $availableMigrations List of available migration classes.
     *
     * @throws MigrationException If applying migrations fails.
     */
    public function migrate(array $availableMigrations) : void
    {
        try {
            $pending = $this->getPendingMigrations(availableMigrations: $availableMigrations);

            if ($pending === []) {
                $this->logInfo(message: "No migrations to execute.");

                return;
            }

            $batchId = $this->migrationRepository->getLatestBatch() + 1;

            foreach ($pending as $migration) {
                $this->runMigration(migration: $migration);
                $this->migrationRepository->save(
                    migration : $migration,
                    executable: $this->resolveFullNamespace($migration),
                    batch     : $batchId
                );
            }
        } catch (Throwable $throwable) {
            throw new MigrationException(
                message : "Failed to execute migrations: " . $throwable->getMessage(),
                previous: $throwable
            );
        }
    }

    /**
     * Get pending migrations by comparing available with executed migrations.
     *
     * @param array $availableMigrations List of available migration classes.
     *
     * @return array List of pending migrations.
     */
    private function getPendingMigrations(array $availableMigrations) : array
    {
        $executed = array_column($this->migrationRepository->getAll(), 'migration');

        return array_diff($availableMigrations, $executed);
    }

    /**
     * Run a single migration.
     *
     * @param string $migration The migration class name.
     *
     * @throws MigrationException If applying the migration fails.
     */
    private function runMigration(string $migration) : void
    {
        try {
            $fullNamespace = $this->resolveFullNamespace($migration);

            if (! class_exists($fullNamespace)) {
                throw new MigrationException(message: "Migration class '" . $fullNamespace . "' not found.");
            }

            $instance = app($fullNamespace);

            if (! $instance instanceof Migration) {
                $this->logInfo(message: "Skipping non-migration class '" . $fullNamespace . "'.");

                return;
            }

            $instance->executeUp();
        } catch (Throwable $throwable) {
            throw new MigrationException(
                message : "Failed to run migration '" . $migration . "': " . $throwable->getMessage(),
                previous: $throwable
            );
        }
    }
}
