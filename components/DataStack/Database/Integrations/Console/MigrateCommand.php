<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\Integrations\Console;

use Avax\Components\DataStack\Database\System\Capabilities\Migrations\LoadMigrations\MigrationLoader;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\RunMigrations\MigrationRepository;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\RunMigrations\MigrationRunner;
use Throwable;

final readonly class MigrateCommand
{
    public function __construct(
        private MigrationRepository $migrationRepository,
        private MigrationRunner     $migrationRunner,
        private MigrationLoader     $migrationLoader,
    ) {}

    public function handle(string $path, bool $dryRun = false) : int
    {
        try {
            if ($dryRun) {
                echo "\033[36mDRY RUN MODE:\033[0m No changes will be executed.\n";
            }

            $this->migrationRepository->ensureTableExists();
            $ran     = $this->migrationRepository->getRan();
            $pending = $this->migrationLoader->getPending(path: $path, ran: $ran);

            if (empty($pending)) {
                echo "\033[36mNothing to migrate.\033[0m\n";

                return 0;
            }

            $this->migrationRunner->up(migrations: $pending, path: $path, dryRun: $dryRun);

            foreach (array_keys(array: $pending) as $name) {
                echo sprintf('  ✓ %s%s', $name, PHP_EOL);
            }

            echo "\033[32mMigrated " . count(value: $pending) . " migration(s).\033[0m\n";

            return 0;
        } catch (Throwable $throwable) {
            echo sprintf('[31mMigration failed:[0m %s%s', $throwable->getMessage(), PHP_EOL);

            return 1;
        }
    }
}
