<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Runner\Commands;

use Avax\Database\Migration\Runner\Generators\CommandInterface;
use Avax\Database\Migration\Runner\MigrationException;
use Avax\Database\Migration\Runner\Repository\MigrationRepositoryInterface;
use Avax\Database\Migration\Runner\SchemaBuilder;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

use function readline;
use function strtolower;
use function trim;

/**
 * InstallCommand handles the initial application setup.
 *
 * This final class is made readonly to ensure immutability after instantiation,
 * which enhances reliability and predictability in its behavior.
 */
final readonly class InstallCommand implements CommandInterface
{
    /**
     * The name of the migrations table used to track migrations.
     */
    private const string MIGRATIONS_TABLE = 'migrations';

    /**
     * Constructor for InstallCommand.
     *
     * @param SchemaBuilder                $schemaBuilder       Builder for database schemas.
     * @param LoggerInterface              $logger              Logger instance for logging events.
     * @param MigrationRepositoryInterface $migrationRepository Repository for handling migration records.
     */
    public function __construct(
        private SchemaBuilder                $schemaBuilder,
        private LoggerInterface              $logger,
        private MigrationRepositoryInterface $migrationRepository
    ) {}

    /**
     * Executes the command to set up the initial database and migrations.
     *
     * @param array $arguments Key-value array of arguments for the command.
     *
     * @throws \Avax\Database\Migration\Runner\MigrationException if any step in the process fails.
     */
    public function execute(array $arguments = []) : void
    {
        $database = $this->getDatabaseName(arguments: $arguments);
        $this->prepareDatabase(database: $database);
        $this->ensureMigrationsTableSetup();
        $this->recordSelfAsFirstMigration();
    }

    /**
     * Retrieves the database name from arguments or environment variables.
     *
     * Throws an exception if the database name is not found.
     *
     * @param array $arguments The command-line arguments passed to the script.
     *
     * @return string The name of the database.
     *
     * @throws \Avax\Database\Migration\Runner\MigrationException if the database name is not provided.
     */
    private function getDatabaseName(array $arguments) : string
    {
        $database = $arguments['database'] ?? env(key: 'DB_NAME') ?? null;
        if (! $database) {
            $this->logAndThrowMigrationException(message: 'Database name is required but was not provided.');
        }

        $this->logger->info(message: 'Preparing installation for database: ' . $database);

        return $database;
    }

    /**
     * Logs an error message and throws a MigrationException.
     *
     * This ensures that each failure point provides a consistent error handling strategy.
     *
     * @param string          $message  The error message to log and throw.
     * @param \Throwable|null $previous The previous exception for chaining, if any.
     *
     * @throws MigrationException Always thrown after logging the error.
     */
    private function logAndThrowMigrationException(string $message, Throwable|null $previous = null) : never
    {
        $this->logger->error(message: $message);
        throw new MigrationException(message: $message, previous: $previous);
    }

    /**
     * Prepares the database for the installation.
     *
     * Checks the database connection and ensures the existence of the database.
     *
     * @param string $database The name of the database.
     *
     * @throws \Avax\Database\Migration\Runner\MigrationException if any step in preparation fails.
     */
    private function prepareDatabase(string $database) : void
    {
        $this->checkDatabaseConnection(database: $database);
        $this->ensureDatabaseExists(database: $database);
    }

    /**
     * Checks if the connection to the database is healthy.
     *
     * Throws an exception if the connection is not healthy, to ensure database operations are safe to proceed.
     *
     * @param string $database The name of the database.
     *
     * @throws \Avax\Database\Migration\Runner\MigrationException if the database connection is unhealthy.
     */
    private function checkDatabaseConnection(string $database) : void
    {
        if (! $this->schemaBuilder->isConnectionHealthy(database: $database)) {
            $this->logAndThrowMigrationException(
                message: sprintf("Failed to establish a healthy connection to the database '%s'.", $database)
            );
        }
    }

    /**
     * Ensures that the database exists, creating it if it does not.
     *
     * Logs and provides feedback to the user accordingly.
     *
     * @param string $database The name of the database.
     *
     * @throws \Avax\Database\Migration\Runner\MigrationException if creating the database fails.
     */
    private function ensureDatabaseExists(string $database) : void
    {
        if (! $this->schemaBuilder->databaseExists(database: $database)) {
            $this->logger->info(message: sprintf("Database '%s' does not exist. Creating database...", $database));
            $this->schemaBuilder->createDatabase(database: $database);
            echo "Database '" . $database . "' created successfully.\n";
        } else {
            echo "Database '" . $database . "' already exists.\n";
        }
    }

    /**
     * Ensures the migrations table is set up correctly.
     *
     * This method handles the creation or recreation of the migrations table, providing feedback and
     * handling exceptions to maintain consistency in the setup process.
     *
     * @throws \Avax\Database\Migration\Runner\MigrationException if setting up the migrations table fails.
     */
    private function ensureMigrationsTableSetup() : void
    {
        try {
            if (! $this->schemaBuilder->tableExists(table: self::MIGRATIONS_TABLE)) {
                $this->createMigrationsTable();
            } else {
                $this->promptRecreateMigrationsTable();
            }

            echo "Migration install completed.\n";
        } catch (RuntimeException $runtimeException) {
            $this->logAndThrowMigrationException(
                message : "Failed to set up migrations table: " . $runtimeException->getMessage(),
                previous: $runtimeException
            );
        }
    }

    /**
     * Creates the migrations table with the necessary columns.
     *
     * The table structure is defined within a callback to ensure consistent setup.
     *
     * @throws \Avax\Database\Migration\Runner\MigrationException if creating the migrations table fails.
     */
    private function createMigrationsTable() : void
    {
        try {
            $this->schemaBuilder->create(table: self::MIGRATIONS_TABLE, callback: static function ($table) : void {
                $table->id();
                $table->string('migration');
                $table->string('executable');
                $table->integer('batch');
                $table->timestamp('executed_at')->useCurrent();
            });
            $this->logger->info(message: 'Migrations table created successfully.');
            echo "Migrations table created successfully.\n";
        } catch (Throwable $throwable) {
            $this->logAndThrowMigrationException(
                message : "Failed to create migrations table: " . $throwable->getMessage(),
                previous: $throwable
            );
        }
    }

    /**
     * Prompts the user to recreate the migration table if it already exists.
     *
     * Provides options to drop and recreate the table or to skip this step.
     *
     * @throws \Avax\Database\Migration\Runner\MigrationException if user opts to recreate and the operation
     *                                                                  fails.
     */
    private function promptRecreateMigrationsTable() : void
    {
        $choice = strtolower(
            trim(readline("The 'migrations' table already exists. Do you want to recreate it? [yes/no]: "))
        );
        if (in_array($choice, ['yes', 'y'], true)) {
            $this->schemaBuilder->drop(table: self::MIGRATIONS_TABLE);
            $this->logger->info(message: 'Old migrations table dropped.');
            echo "Old 'migrations' table dropped.\n";
            $this->createMigrationsTable();
        } else {
            echo "Skipped creating the 'migrations' table.\n";
        }
    }

    /**
     * Records this InstallCommand as the first migration in the migrations table.
     *
     * This method ensures that InstallCommand is logged as the first entry,
     * establishing the provenance of the migration system installation.
     *
     * @throws \Avax\Database\Migration\Runner\MigrationException if saving the record fails.
     */
    private function recordSelfAsFirstMigration() : void
    {
        try {
            $this->migrationRepository->save(
                migration : 'CreateMigrationsTable',
                executable: self::class,
                batch     : 1
            );
            $this->logger->info(message: 'Recorded InstallCommand as the first migration.');
            echo "Recorded InstallCommand as the first migration.\n";
        } catch (Throwable $throwable) {
            $this->logAndThrowMigrationException(
                message : "Failed to record the InstallCommand migration: " . $throwable->getMessage(),
                previous: $throwable
            );
        }
    }
}