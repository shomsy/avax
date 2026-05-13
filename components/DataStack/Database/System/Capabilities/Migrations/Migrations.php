<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Migrations;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\Connections;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\CreateMigration\MigrationGenerator;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\ExportDatabase\DatabaseExporter;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\LoadMigrations\MigrationLoader;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\ReadMigrationStatus\ReadMigrationStatus;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\RollbackMigrations\RollbackMigrations;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\RunMigrations\MigrationRepository;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\RunMigrations\MigrationRunner;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Schema\Schema;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\SeedDatabase\Seeder;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Query;
use Avax\Components\DataStack\Database\System\Capabilities\Transactions\Transactions;
use InvalidArgumentException;
use ReflectionException;
use Throwable;

/**
 * Laravel-style migration capability layered over the Database system.
 */
final readonly class Migrations
{
    public function __construct(
        private Query         $query,
        private Connections   $connections,
        private Transactions  $transactions,
        private Schema        $schema,
    ) {
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function create(string $table, callable $callback, string|null $connectionName = null) : void
    {
        $this->schema->create(table: $table, callback: $callback, connectionName: $connectionName);
    }

    public function schema(): Schema
    {
        return $this->schema;
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function dropIfExists(string $table, string|null $connectionName = null) : void
    {
        $this->schema()->dropIfExists(table: $table, connectionName: $connectionName);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function truncate(string $table, string|null $connectionName = null) : void
    {
        $this->schema()->truncate(table: $table, connectionName: $connectionName);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function createDatabase(string $name, string|null $connectionName = null) : void
    {
        $this->schema()->createDatabase(name: $name, connectionName: $connectionName);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function dropDatabase(string $name, string|null $connectionName = null) : void
    {
        $this->schema()->dropDatabase(name: $name, connectionName: $connectionName);
    }

    public function generator(): MigrationGenerator
    {
        return new MigrationGenerator();
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function exporter(string|null $connectionName = null) : DatabaseExporter
    {
        return new DatabaseExporter(pdo: $this->connections->pdo(name: $connectionName));
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function rollbacker(string|null $connectionName = null) : RollbackMigrations
    {
        return new RollbackMigrations(
            repository: $this->repository(connectionName: $connectionName),
            runner    : $this->runner(connectionName: $connectionName),
            loader    : $this->loader(),
        );
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function repository(string|null $connectionName = null) : MigrationRepository
    {
        return new MigrationRepository(
            schema : $this->schema(),
            builder: $this->builder(connectionName: $connectionName),
        );
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function builder(string|null $connectionName = null) : QueryBuilder
    {
        return $this->query->builder(connectionName: $connectionName);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function runner(string|null $connectionName = null) : MigrationRunner
    {
        return new MigrationRunner(
            transactions  : $this->transactions,
            connectionName: $connectionName,
            repository    : $this->repository(connectionName: $connectionName),
            builder       : $this->builder(connectionName: $connectionName),
        );
    }

    public function loader(): MigrationLoader
    {
        return new MigrationLoader();
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function status(string|null $connectionName = null) : ReadMigrationStatus
    {
        return new ReadMigrationStatus(
            repository: $this->repository(connectionName: $connectionName),
            loader    : $this->loader(),
        );
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function seed(Seeder|string $seeder, string|null $connectionName = null) : void
    {
        $instance = is_string(value: $seeder) ? new $seeder() : $seeder;

        if (! $instance instanceof Seeder) {
            throw new InvalidArgumentException(message: 'Seed target must extend the base Seeder class.');
        }

        $instance->withBuilder(builder: $this->builder(connectionName: $connectionName))->run();
    }
}
