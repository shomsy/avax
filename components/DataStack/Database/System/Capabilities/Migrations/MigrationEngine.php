<?php
declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Migrations;

final readonly class MigrationEngine
{
    public function __construct(
        private MigrationRepository $repository,
        private string $migrationsPath
    ) {}

    public function migrate(): void
    {
        $this->repository->ensureTableExists();
        $ran = $this->repository->getRan();
        $files = glob($this->migrationsPath . '/*.php');
        $batch = $this->repository->getLastBatchNumber() + 1;

        foreach ($files as $file) {
            $name = basename($file, '.php');
            if (in_array($name, $ran)) continue;

            echo "Migrating: $name\n";
            $migration = require $file;
            $migration->up();
            
            $this->repository->log($name, $batch);
            echo "Migrated: $name\n";
        }
    }
}
