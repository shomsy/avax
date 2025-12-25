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
final class MigrateCommand
{
    public function __construct(
        private readonly MigrationRepository $repository,
        private readonly MigrationRunner     $runner,
        private readonly MigrationLoader     $loader
    ) {}

    public function handle(string $path, bool $dryRun = false) : int
    {
        if ($dryRun) {
            $this->info('DRY RUN MODE: No changes will be executed.');
        }

        $this->info('Running migrations...');
        $this->repository->ensureTableExists();

        $ran = $this->repository->getRan();

        // --- Enterprise Integrity Check ---
        $ranMap = array_column($ran, 'checksum', 'migration');
        foreach ($ran as $record) {
            $name       = $record['migration'];
            $dbChecksum = $record['checksum'];

            if ($dbChecksum) {
                $fileChecksum = $this->loader->getChecksum($name, $path);
                if ($fileChecksum && $dbChecksum !== $fileChecksum) {
                    $this->error("CRITICAL: Migration integrity violation in '{$name}'.");
                    $this->error("The file has been modified after execution. Please revert changes.");

                    return 1;
                }
            }
        }

        $pending = $this->loader->getPending($path, $ran);

        if (empty($pending)) {
            $this->info('Nothing to migrate.');

            return 0;
        }

        $this->info(sprintf('Found %d pending migration(s).', count($pending)));

        try {
            $this->runner->up($pending, $path, $dryRun);

            if ($dryRun) {
                $this->success('Dry run completed successfully. No changes made.');
            } else {
                $this->success(sprintf('Migrated %d migration(s) successfully!', count($pending)));
            }

            foreach (array_keys($pending) as $name) {
                echo "  ✓ {$name}\n";
            }

            return 0;
        } catch (Throwable $e) {
            $this->error('Migration failed: ' . $e->getMessage());

            return 1;
        }
    }

    private function info(string $msg) : void
    {
        echo "\033[36m{$msg}\033[0m\n";
    }

    private function error(string $msg) : void
    {
        echo "\033[31m{$msg}\033[0m\n";
    }

    private function success(string $msg) : void
    {
        echo "\033[32m{$msg}\033[0m\n";
    }
}
