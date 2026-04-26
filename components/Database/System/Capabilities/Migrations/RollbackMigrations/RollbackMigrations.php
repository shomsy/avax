<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Migrations\RollbackMigrations;

use Avax\Database\System\Capabilities\Migrations\LoadMigrations\MigrationLoader;
use Avax\Database\System\Capabilities\Migrations\RunMigrations\MigrationRepository;
use Avax\Database\System\Capabilities\Migrations\RunMigrations\MigrationRunner;
use Throwable;

/**
 * Coordinates one rollback pass from migration records and loaded files.
 */
final readonly class RollbackMigrations
{
    public function __construct(
        private MigrationRepository $repository,
        private MigrationRunner     $runner,
        private MigrationLoader     $loader
    ) {}

    /**
     * @return string[]
     *
     * @throws Throwable
     */
    public function run(string $path, int $steps = 1) : array
    {
        $records = $this->repository->getLastBatch(steps: $steps);

        if (empty($records)) {
            return [];
        }

        $all        = $this->loader->load(path: $path);
        $toRollback = [];

        foreach ($records as $record) {
            $name = $record['migration'];

            if (isset($all[$name])) {
                $toRollback[] = $all[$name];
            }
        }

        $this->runner->rollback(migrations: $toRollback, steps: $steps);

        return array_column(array: $records, column_key: 'migration');
    }
}
