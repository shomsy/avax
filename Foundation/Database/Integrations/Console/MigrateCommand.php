<?php

declare(strict_types=1);

namespace Avax\Database\Integrations\Console;

use Avax\Database\System\Capabilities\Migrations\LoadMigrations\MigrationLoader;
use Avax\Database\System\Capabilities\Migrations\RunMigrations\MigrationRepository;
use Avax\Database\System\Capabilities\Migrations\RunMigrations\MigrationRunner;
use Throwable;

final readonly class MigrateCommand
{
    public function __construct(
        private MigrationRepository $repository,
        private MigrationRunner     $runner,
        private MigrationLoader     $loader
    ) {}

    public function handle(string $path, bool $dryRun = false) : int
    {
        try {
            if ($dryRun) {
                echo "\033[36mDRY RUN MODE:\033[0m No changes will be executed.\n";
            }

            $this->repository->ensureTableExists();
            $ran     = $this->repository->getRan();
            $pending = $this->loader->getPending(path: $path, ran: $ran);

            if (empty($pending)) {
                echo "\033[36mNothing to migrate.\033[0m\n";

                return 0;
            }

            $this->runner->up(migrations: $pending, path: $path, dryRun: $dryRun);

            foreach (array_keys(array: $pending) as $name) {
                echo "  ✓ {$name}\n";
            }

            echo "\033[32mMigrated " . count(value: $pending) . " migration(s).\033[0m\n";

            return 0;
        } catch (Throwable $throwable) {
            echo "\033[31mMigration failed:\033[0m {$throwable->getMessage()}\n";

            return 1;
        }
    }
}
