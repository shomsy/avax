<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Runner;

use Avax\Database\Migration\Runner\Exception\MigrationException;
use Avax\Database\Migration\Runner\Service\MigrationExecution;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Abstract Migration Base Class
 *
 * Provides the foundation for creating database migrations in the Avax Foundation.
 * Supports transactional, auditable, API-driven migration execution with up/down lifecycle.
 */
abstract readonly class Migration
{
    /**
     * Message template for successful operation logging.
     *
     * @var string
     */
    protected const string LOG_OPERATION_SUCCESS = 'Successfully executed: %s';

    /**
     * Message template for failed operation logging.
     *
     * @var string
     */
    protected const string LOG_OPERATION_FAILURE = 'Failed to execute: %s. Error: %s';

    /**
     * Message template for exception escalation during migration.
     *
     * @var string
     */
    protected const string MIGRATION_ERROR = 'Migration error during: %s. Details: %s';

    /**
     * Dependency for schema creation and modification.
     *
     * @var SchemaBuilder
     */
    protected SchemaBuilder $schemaBuilder;

    /**
     * Action for managing migration registration and persistence.
     *
     * @var MigrationExecution
     */
    protected MigrationExecution $migrationService;

    /**
     * Optional logger for structured output.
     *
     * @var LoggerInterface|null
     */
    protected LoggerInterface|null $logger;

    /**
     * Constructs the Migration base.
     *
     * @param SchemaBuilder        $schemaBuilder    DSL engine for table/column mutation.
     * @param MigrationExecution   $migrationService Action to persist execution records.
     * @param LoggerInterface|null $logger           Optional logger.
     */
    public function __construct(
        SchemaBuilder        $schemaBuilder,
        MigrationExecution   $migrationService,
        LoggerInterface|null $logger = null
    ) {
        $this->schemaBuilder    = $schemaBuilder;
        $this->migrationService = $migrationService;
        $this->logger           = $logger;
    }

    /**
     * Executes the "up" migration lifecycle.
     *
     * @throws MigrationException
     * @throws \Throwable
     */
    final public function executeUp() : void
    {
        $name  = $this->getMigrationName();
        $batch = $this->migrationService->getLatestBatch() + 1;

        $this->logInfo(message: sprintf("🔼 Starting migration '%s' (up)...", $name));

        try {
            $this->executeSafely(
                operation  : fn() => $this->up(),
                description: sprintf("Applying migration '%s'", $name)
            );

            $this->migrationService->save(
                migration : $name,
                executable: 'up()',
                batch     : $batch
            );

            $this->logInfo(message: sprintf("🧱 Migration '%s' completed successfully (up).", $name));
        } catch (Throwable $e) {
            throw new MigrationException(
                message : sprintf("Migration '%s' failed: %s", $name, $e->getMessage()),
                previous: $e
            );
        }
    }

    /**
     * Resolves the class-based migration name.
     *
     * @return string
     */
    private function getMigrationName() : string
    {
        return static::class;
    }

    /**
     * Logs a message if logger is available.
     *
     * @param string $message
     */
    private function logInfo(string $message) : void
    {
        $this->logger?->info(message: $message);
    }

    /**
     * Wraps any logic in try/catch, logs success/failure, escalates errors.
     *
     * @param callable $operation
     * @param string   $description
     *
     * @throws MigrationException
     */
    private function executeSafely(callable $operation, string $description) : void
    {
        // Begin a try block to handle potential errors during operation execution.
        try {
            // Execute the passed operation.
            // Any exception thrown here will be caught by the catch block below.
            $operation();

            $this->logInfo(message: sprintf(self::LOG_OPERATION_SUCCESS, $description));
        } catch (Throwable $e) {
            $this->logError(message: sprintf(self::LOG_OPERATION_FAILURE, $description, $e->getMessage()));

            // Log the failure of the operation with the error message for debugging or auditing purposes.
            $this->logError(sprintf(self::LOG_OPERATION_FAILURE, $description, $e->getMessage()));

            // Throw a MigrationException to escalate the issue while providing context for the failure.
            throw new MigrationException(
                message : sprintf(self::MIGRATION_ERROR, $description, $e->getMessage()),
                previous: $e
            );
        }
    }

    /**
     * Logs an error if logger is available.
     *
     * @param string $message
     */
    private function logError(string $message) : void
    {
        $this->logger?->error(message: $message);
    }

    /**
     * Abstract method to be implemented by concrete migrations.
     *
     * @return void
     */
    abstract protected function up() : void;

    /**
     * Executes the "down" rollback lifecycle.
     *
     * @throws MigrationException
     */
    final public function executeDown() : void
    {
        $name = $this->getMigrationName();

        $this->logInfo(message: sprintf("🔽 Starting migration '%s' (down)...", $name));

        try {
            $this->executeSafely(
                operation  : fn() => $this->down(),
                description: sprintf("Reverting migration '%s'", $name)
            );

            $this->migrationService->delete(migration: $name);

            $this->logInfo(message: sprintf("🗑️ Migration '%s' completed successfully (down).", $name));
        } catch (Throwable $e) {
            throw new MigrationException(
                message : sprintf("Rollback for '%s' failed: %s", $name, $e->getMessage()),
                previous: $e
            );
        }
    }

    /**
     * Abstract method for rollback.
     *
     * @return void
     */
    abstract protected function down() : void;
}
