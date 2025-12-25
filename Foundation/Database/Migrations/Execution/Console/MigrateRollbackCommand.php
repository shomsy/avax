<?php

declare(strict_types=1);

namespace Avax\Migrations\Execution\Console;

use Avax\Migrations\Execution\Repository\MigrationRepository;
use Avax\Migrations\Execution\Runner\MigrationRunner;
use Avax\Migrations\Generate\MigrationLoader;
use Throwable;

/**
 * Console command to rollback migrations.
 */
final class MigrateRollbackCommand
{
    public function __construct(
        private readonly MigrationRepository $repository,
        private readonly MigrationRunner     $runner,
        private readonly MigrationLoader     $loader
    ) {}

    public function handle(string $path, int $steps = 1) : int
    {
        $this->info("Rolling back {$steps} batch(es)...");

        $records = $this->repository->getLastBatch($steps);
        if (empty($records)) {
            $this->info('Nothing to rollback.');

            return 0;
        }

        $all        = $this->loader->load($path);
        $toRollback = [];
        foreach ($records as $record) {
            $name = $record['migration'];
            if (isset($all[$name])) {
                $toRollback[] = $all[$name];
            }
        }

        try {
            $this->runner->rollback($toRollback, $steps);
            $this->success(sprintf('Rolled back %d migration(s) successfully!', count($toRollback)));
            foreach ($records as $record) {
                echo "  ✓ {$record['migration']}\n";
            }

            return 0;
        } catch (Throwable $e) {
            $this->error('Rollback failed: ' . $e->getMessage());

            return 1;
        }
    }

    private function info(string $msg) : void
    {
        echo "\033[36m{$msg}\033[0m\n";
    }

    private function success(string $msg) : void
    {
        echo "\033[32m{$msg}\033[0m\n";
    }

    private function error(string $msg) : void
    {
        echo "\033[31m{$msg}\033[0m\n";
    }
}
