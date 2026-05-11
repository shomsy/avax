<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Migrations\RunMigrations;

use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Exceptions\MigrationException;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder;
use Avax\Components\DataStack\Database\System\Capabilities\Transactions\Transactions;
use Throwable;

/**
 * Technical supervisor responsible for the execution lifecycle of migrations.
 *
 * -- intent: coordinate the UP/DOWN operations of migrations and update the system audit trail.
 */
final readonly class MigrationRunner
{
    public function __construct(private MigrationRepository $migrationRepository, private QueryBuilder $queryBuilder, private Transactions $transactions, private string|null $connectionName = null)
    {
    }

    public function up(array $migrations, string $path, bool $dryRun = false): void
    {
        try {
            $builder = $dryRun ? $this->queryBuilder->pretend() : $this->queryBuilder;
            $ran = $this->migrationRepository->getRan();
            $ranNames = array_column(array: $ran, column_key: 'migration');
            $batch = $this->migrationRepository->getNextBatchNumber();

            foreach ($migrations as $name => $migration) {
                if (in_array(needle: $name, haystack: $ranNames, strict: true)) {
                    continue;
                }

                $checksum = md5_file(filename: rtrim(string: $path, characters: DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$name.'.php');
                $this->runMigration(
                    migration: $migration,
                    method   : 'up',
                    name     : $name,
                    batch    : $batch,
                    checksum : $checksum,
                    builder  : $builder,
                );
            }
        } catch (Throwable $throwable) {
            throw new MigrationException(migrationClass: 'Runner', message: $throwable->getMessage(), previous: $throwable);
        }
    }

    private function runMigration(
        mixed $migration,
        string $method,
        string $name,
        QueryBuilder $queryBuilder, int|null $batch = null, string|null $checksum = null,
    ): void {
        try {
            // Inject QueryBuilder into migration if it supports it
            if (method_exists(object_or_class: $migration, method: 'setQueryBuilder')) {
                $migration->setQueryBuilder($queryBuilder);
            }

            $this->transactions->run(callback      : function () use ($migration, $method, $name, $batch, $checksum): void {
                $migration->{$method}();

                if ($method === 'up') {
                    $this->migrationRepository->log(name: $name, batch: (int) $batch, checksum: $checksum);
                } else {
                    $this->migrationRepository->remove(name: $name);
                }
            }, connectionName: $this->connectionName);
        } catch (Throwable $throwable) {
            throw new MigrationException(
                migrationClass: $name,
                message       : sprintf('Failed during [%s]: ', $method).$throwable->getMessage(),
                previous      : $throwable,
            );
        }
    }

    public function rollback(array $migrations, int $steps = 1): void
    {
        try {
            $migrationsByName = [];
            foreach ($migrations as $migration) {
                $migrationsByName[$migration::class] = $migration;
            }

            $records = $this->migrationRepository->getLastBatch(steps: $steps);

            foreach ($records as $record) {
                $name = $record['migration'];

                if (isset($migrationsByName[$name])) {
                    $this->runMigration(
                        migration: $migrationsByName[$name],
                        method   : 'down',
                        name     : $name,
                        builder  : $this->queryBuilder,
                    );
                }
            }
        } catch (Throwable $throwable) {
            throw new MigrationException(migrationClass: 'Runner', message: $throwable->getMessage(), previous: $throwable);
        }
    }
}
