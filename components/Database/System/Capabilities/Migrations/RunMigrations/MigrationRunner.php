<?php

declare(strict_types=1);

namespace Avax\Components\Database\System\Capabilities\Migrations\RunMigrations;

use Avax\Components\Database\System\Capabilities\Migrations\Exceptions\MigrationException;
use Avax\Components\Database\System\Capabilities\Query\Builder\QueryBuilder;
use Avax\Components\Database\System\Capabilities\Transactions\Transactions;
use Throwable;

/**
 * Technical supervisor responsible for the execution lifecycle of migrations.
 *
 * -- intent: coordinate the UP/DOWN operations of migrations and update the system audit trail.
 */
final readonly class MigrationRunner
{
    private QueryBuilder        $builder;
    private MigrationRepository $repository;
    private Transactions        $transactions;
    private string|null         $connectionName;

    public function __construct(
        MigrationRepository $repository,
        QueryBuilder        $builder,
        Transactions        $transactions,
        string|null         $connectionName = null
    )
    {
        $this->repository     = $repository;
        $this->builder        = $builder;
        $this->transactions   = $transactions;
        $this->connectionName = $connectionName;
    }

    public function up(array $migrations, string $path, bool $dryRun = false) : void
    {
        try {
            $builder  = $dryRun ? $this->builder->pretend() : $this->builder;
            $ran      = $this->repository->getRan();
            $ranNames = array_column(array: $ran, column_key: 'migration');
            $batch    = $this->repository->getNextBatchNumber();

            foreach ($migrations as $name => $migration) {
                if (in_array(needle: $name, haystack: $ranNames, strict: true)) {
                    continue;
                }

                $checksum = md5_file(filename: rtrim(string: $path, characters: DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $name . '.php');
                $this->runMigration(
                    migration: $migration,
                    method   : 'up',
                    name     : $name,
                    builder  : $builder,
                    batch    : $batch,
                    checksum : $checksum
                );
            }
        } catch (Throwable $e) {
            throw new MigrationException(migrationClass: 'Runner', message: $e->getMessage(), previous: $e);
        }
    }

    private function runMigration(
        mixed        $migration,
        string       $method,
        string       $name,
        QueryBuilder $builder,
        int|null     $batch = null,
        string|null  $checksum = null
    ) : void
    {
        try {
            // Inject QueryBuilder into migration if it supports it
            if (method_exists(object_or_class: $migration, method: 'setQueryBuilder')) {
                $migration->setQueryBuilder($builder);
            }

            $this->transactions->run(callback      : function () use ($migration, $method, $name, $batch, $checksum) {
                $migration->{$method}();

                if ($method === 'up') {
                    $this->repository->log(name: $name, batch: (int) $batch, checksum: $checksum);
                } else {
                    $this->repository->remove(name: $name);
                }
            },                       connectionName: $this->connectionName);
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
                    $this->runMigration(
                        migration: $migrationsByName[$name],
                        method   : 'down',
                        name     : $name,
                        builder  : $this->builder
                    );
                }
            }
        } catch (Throwable $e) {
            throw new MigrationException(migrationClass: 'Runner', message: $e->getMessage(), previous: $e);
        }
    }
}
