<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\Integrations\Console;

use Avax\Components\DataStack\Database\System\Capabilities\Migrations\RollbackMigrations\RollbackMigrations;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\RunMigrations\MigrationRepository;
use Throwable;

final readonly class MigrateRefreshCommand
{
    public function __construct(
        private RollbackMigrations  $rollbackMigrations,
        private MigrationRepository $repository,
        private MigrateCommand      $migrateCommand
    ) {}

    public function handle(string $path, bool $dryRun = false) : int
    {
        try {
            $this->repository->ensureTableExists();
            $rolledBack = $this->rollbackMigrations->run(path: $path, steps: PHP_INT_MAX);

            if (! empty($rolledBack)) {
                foreach ($rolledBack as $name) {
                    echo "  ✓ {$name}\n";
                }

                echo "\033[32mRolled back " . count(value: $rolledBack) . " migration(s).\033[0m\n";
            }

            return $this->migrateCommand->handle(path: $path, dryRun: $dryRun);
        } catch (Throwable $throwable) {
            echo "\033[31mRefresh failed:\033[0m {$throwable->getMessage()}\n";

            return 1;
        }
    }
}
