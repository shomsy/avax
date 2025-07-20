<?php

declare(strict_types=1);

namespace Gemini\Database\Migration\Runner\Commands;

use Gemini\Database\Migration\Runner\Exception\MigrationException;
use Gemini\Database\Migration\Runner\Generators\CommandInterface;
use Gemini\Database\Migration\Runner\Repository\MigrationRepositoryInterface;
use Gemini\Database\Migration\Runner\Service\MigrationStateManager;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * MigrateCommand
 *
 * A final immutable class responsible for executing the migration process.
 * Implements CommandInterface to standardize command execution.
 *
 * The class is marked as readonly to enforce immutability ensuring
 * that its state cannot be altered after instantiation, adding robustness
 * and thread-safety.
 */
final readonly class MigrateCommand implements CommandInterface
{
    /**
     * MigrateCommand constructor.
     *
     * @param MigrationStateManager        $migrationStateManager Service to manage the state of migrations.
     * @param MigrationRepositoryInterface $migrationRepository   Repository to fetch available migrations.
     * @param LoggerInterface              $logger                Logger for recording operational events.
     */
    public function __construct(
        private MigrationStateManager        $migrationStateManager,
        private MigrationRepositoryInterface $migrationRepository,
        private LoggerInterface              $logger
    ) {}

    /**
     * Executes the migration process.
     *
     * This method orchestrates the entire migration process, logging important steps
     * and handling exceptions to ensure smooth operation.
     *
     * @param array $arguments CLI arguments or configuration parameters.
     *
     * @throws MigrationException If the migration process encounters an error.
     */
    public function execute(array $arguments = []) : void
    {
        try {
            $this->logger->info(message: 'Starting migration process.');
            echo "Starting migration process...\n";

            $availableMigrations = $this->fetchAvailableMigrations();
            $this->migrationStateManager->migrate(availableMigrations: $availableMigrations);

            $this->logger->info(message: 'Migration process completed successfully.');
            echo "Migration process completed successfully.\n";
        } catch (MigrationException $migrationException) {
            // Handle known migration-specific errors.
            $this->handleError(migrationException: $migrationException);
        } catch (Throwable $throwable) {
            // Handle unexpected errors that do not fall under MigrationException.
            $this->handleUnexpectedError(throwable: $throwable);
        }
    }

    /**
     * Fetches available migrations directly from the database.
     *
     * This encapsulates the retrieval logic from the repository, ensuring a single responsibility
     * and making it easy to modify data fetching strategy if required.
     *
     * @return array List of fully qualified migration class names.
     */
    private function fetchAvailableMigrations() : array
    {
        $migrations          = $this->migrationRepository->getAll();
        $availableMigrations = array_column($migrations, 'executable');

        $this->logger->info(message: 'Fetched available migrations.', context: ['migrations' => $availableMigrations]);

        return $availableMigrations;
    }

    /**
     * Handles migration-specific errors gracefully.
     *
     * This function centralizes error handling for migration exceptions, ensuring consistent
     * logging and error reporting which makes debugging easier.
     *
     * @param MigrationException $migrationException Exception to handle.
     */
    private function handleError(MigrationException $migrationException) : void
    {
        $this->logger->error(
            message: 'Migration process failed.',
            context: ['error' => $migrationException->getMessage()]
        );

        echo sprintf('Migration process failed: %s%s', $migrationException->getMessage(), PHP_EOL);
    }

    /**
     * Handles unexpected errors gracefully.
     *
     * Centralizes the handling of unknown or unexpected errors, ensuring that critical failures
     * are logged and reported consistently, making it easier to track issues.
     *
     * @param Throwable $throwable Exception to handle.
     */
    private function handleUnexpectedError(Throwable $throwable) : void
    {
        $this->logger->critical(
            message: 'An unexpected error occurred during the migration process.',
            context: ['error' => $throwable->getMessage()]
        );

        echo sprintf('Unexpected error: %s%s', $throwable->getMessage(), PHP_EOL);
    }
}