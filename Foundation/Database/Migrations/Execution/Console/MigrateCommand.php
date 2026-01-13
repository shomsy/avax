<?php

declare(strict_types=1);

namespace Avax\Migrations\Execution\Console;

use Avax\Migrations\Execution\Repository\MigrationRepository;
use Avax\Migrations\Execution\Runner\MigrationRunner;
use Avax\Migrations\Generate\MigrationLoader;
use Throwable;

/**
 * Console command to run pending migrations.
 */
final readonly class MigrateCommand
{
    public function __construct(
        private MigrationRepository $repository,
        private MigrationRunner $runner,
        private MigrationLoader $loader
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(string $path, bool $dryRun = false): int
    {
        if ($dryRun) {
            $this->info(msg: 'DRY RUN MODE: No changes will be executed.');
        }

        $this->info(msg: 'Running migrations...');
        $this->repository->ensureTableExists();

        $ran = $this->repository->getRan();

        // --- Enterprise Integrity Check ---
        $ranMap = array_column(array: $ran, column_key: 'checksum', index_key: 'migration');
        foreach ($ran as $record) {
            $name = $record['migration'];
            $dbChecksum = $record['checksum'];

            if ($dbChecksum) {
                $fileChecksum = $this->loader->getChecksum(name: $name, path: $path);
                if ($fileChecksum && $dbChecksum !== $fileChecksum) {
                    $this->error(msg: "CRITICAL: Migration integrity violation in '{$name}'.");
                    $this->error(msg: 'The file has been modified after execution. Please revert changes.');

                    return 1;
                }
            }
        }

        $pending = $this->loader->getPending(path: $path, ran: $ran);

        if (empty($pending)) {
            $this->info(msg: 'Nothing to migrate.');

            return 0;
        }

        $this->info(msg: sprintf('Found %d pending migration(s).', count(value: $pending)));

        try {
            $this->runner->up(migrations: $pending, path: $path, dryRun: $dryRun);

            if ($dryRun) {
                $this->success(msg: 'Dry run completed successfully. No changes made.');
            } else {
                $this->success(msg: sprintf('Migrated %d migration(s) successfully!', count(value: $pending)));
            }

            foreach (array_keys(array: $pending) as $name) {
                echo "  ✓ {$name}\n";
            }

            return 0;
        } catch (Throwable $e) {
            $this->error(msg: 'Migration failed: '.$e->getMessage());

            return 1;
        }
    }

    private function info(string $msg): void
    {
        echo "\033[36m{$msg}\033[0m\n";
    }

    private function error(string $msg): void
    {
        echo "\033[31m{$msg}\033[0m\n";
    }

    private function success(string $msg): void
    {
        echo "\033[32m{$msg}\033[0m\n";
    }
}
