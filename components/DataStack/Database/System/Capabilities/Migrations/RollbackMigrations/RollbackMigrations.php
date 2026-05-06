<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Migrations\RollbackMigrations;

use Avax\Components\DataStack\Database\System\Capabilities\Migrations\LoadMigrations\MigrationLoader;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\RunMigrations\MigrationRepository;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\RunMigrations\MigrationRunner;
use Throwable;

/**
 * Coordinates one rollback pass from migration records and loaded files.
 */
final readonly class RollbackMigrations
{
    public function __construct(
        private MigrationRepository $migrationRepository,
        private MigrationRunner $migrationRunner,
        private MigrationLoader $migrationLoader,
    ) {
    }

    /**
     * @return string[]
     *
     * @throws Throwable
     */
    public function run(string $path, int $steps = 1): array
    {
        $records = $this->migrationRepository->getLastBatch(steps: $steps);

        if ($records === []) {
            return [];
        }

        $all = $this->migrationLoader->load(path: $path);
        $toRollback = [];

        foreach ($records as $record) {
            $name = $record['migration'];

            if (isset($all[$name])) {
                $toRollback[] = $all[$name];
            }
        }

        $this->migrationRunner->rollback(migrations: $toRollback, steps: $steps);

        return array_column(array: $records, column_key: 'migration');
    }
}
