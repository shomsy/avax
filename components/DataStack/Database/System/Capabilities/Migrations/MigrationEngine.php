<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Migrations;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;

final readonly class MigrationEngine
{
    public function __construct(
        private MigrationRepository $migrationRepository,
        private Filesystem $filesystem,
        private string $migrationsPath,
    ) {
    }

    public function migrate(): void
    {
        $this->migrationRepository->ensureTableExists();
        $ran = $this->migrationRepository->getRan();
        $files = $this->filesystem->listFilesByPattern($this->migrationsPath . '/*.php');
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
