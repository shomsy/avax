<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Migrations;

final readonly class MigrationEngine
{
    public function __construct(
        private MigrationRepository $migrationRepository,
        private string $migrationsPath,
    ) {
    }

    public function migrate(): void
    {
        $this->migrationRepository->ensureTableExists();
        $ran = $this->migrationRepository->getRan();
        $files = glob($this->migrationsPath.'/*.php');
        $batch = $this->migrationRepository->getLastBatchNumber() + 1;

        foreach ($files as $file) {
            $name = basename($file, '.php');
            if (in_array($name, $ran)) {
                continue;
            }

            echo sprintf('Migrating: %s%s', $name, PHP_EOL);
            $migration = require $file;
            $migration->up();

            $this->migrationRepository->log($name, $batch);
            echo sprintf('Migrated: %s%s', $name, PHP_EOL);
        }
    }
}
