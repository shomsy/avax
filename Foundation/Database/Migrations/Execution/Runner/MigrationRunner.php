<?php

declare(strict_types=1);

namespace Avax\Migrations\Execution\Runner;

use Avax\Database\QueryBuilder\Core\Builder\QueryBuilder;
use Avax\Migrations\Exceptions\MigrationException;
use Avax\Migrations\Execution\Repository\MigrationRepository;
use Throwable;

/**
 * Technical supervisor responsible for the execution lifecycle of migrations.
 *
 * -- intent: coordinate the UP/DOWN operations of migrations and update the system audit trail.
 */
final readonly class MigrationRunner
{
    public function __construct(
        private MigrationRepository $repository,
        private QueryBuilder        $builder
    ) {}

    public function up(array $migrations, string $path, bool $dryRun = false) : void
    {
        if ($dryRun) {
            $this->builder->pretend();
        }

        try {
            $ran      = $this->repository->getRan();
            $ranNames = array_column(array: $ran, column_key: 'migration');
            $batch    = $this->repository->getNextBatchNumber();

            foreach ($migrations as $name => $migration) {
                if (in_array(needle: $name, haystack: $ranNames, strict: true)) {
                    continue;
                }

                $checksum = md5_file(filename: rtrim(string: $path, characters: DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $name . '.php');
                $this->runMigration(migration: $migration, method: 'up', name: $name, batch: $batch, checksum: $checksum);
            }
        } catch (Throwable $e) {
            throw new MigrationException(migrationClass: 'Runner', message: $e->getMessage(), previous: $e);
        }
    }

    private function runMigration($migration, string $method, string $name, int|null $batch = null, string|null $checksum = null) : void
    {
        try {
            // Inject QueryBuilder into migration if it supports it
            if (method_exists(object_or_class: $migration, method: 'setQueryBuilder')) {
                $migration->setQueryBuilder($this->builder);
            }

            $this->builder->transaction(callback: function () use ($migration, $method, $name, $batch, $checksum) {
                $migration->{$method}();

                if ($method === 'up') {
                    $this->repository->log(name: $name, batch: (int) $batch, checksum: $checksum);
                } else {
                    $this->repository->remove(name: $name);
                }
            });
        } catch (Throwable $e) {
            throw new MigrationException(
                migrationClass: $name,
                message       : "Failed during [{$method}]: " . $e->getMessage(),
                previous      : $e
            );
        }
    }

    public function rollback(array $migrations, int $steps = 1) : void
    {
        try {
            $migrationsByName = [];
            foreach ($migrations as $m) {
                $migrationsByName[$m::class] = $m;
            }

            $records = $this->repository->getLastBatch(steps: $steps);

            foreach ($records as $record) {
                $name = $record['migration'];

                if (isset($migrationsByName[$name])) {
                    $this->runMigration(migration: $migrationsByName[$name], method: 'down', name: $name);
                }
            }
        } catch (Throwable $e) {
            throw new MigrationException(migrationClass: 'Runner', message: $e->getMessage(), previous: $e);
        }
    }
}
