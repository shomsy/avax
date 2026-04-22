<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Migrations;

use Avax\Database\System\Capabilities\Migrations\CreateMigration\MigrationGenerator;
use Avax\Database\System\Capabilities\Migrations\ExportDatabase\DatabaseExporter;
use Avax\Database\System\Capabilities\Migrations\LoadMigrations\MigrationLoader;
use Avax\Database\System\Capabilities\Migrations\ReadMigrationStatus\ReadMigrationStatus;
use Avax\Database\System\Capabilities\Migrations\RollbackMigrations\RollbackMigrations;
use Avax\Database\System\Capabilities\Migrations\RunMigrations\MigrationRepository;
use Avax\Database\System\Capabilities\Migrations\RunMigrations\MigrationRunner;
use Avax\Database\System\Capabilities\Migrations\SchemaOperations\CreateDatabase;
use Avax\Database\System\Capabilities\Migrations\SchemaOperations\DropDatabase;
use Avax\Database\System\Capabilities\Migrations\SchemaOperations\DropTable;
use Avax\Database\System\Capabilities\Migrations\SchemaOperations\TruncateTable;
use Avax\Database\System\Capabilities\Querying\Builder\QueryBuilder;
use Avax\Database\System\Capabilities\Querying\Querying;
use Avax\Database\System\Capabilities\Transactions\Transactions;
use ReflectionException;
use Throwable;

/**
 * Laravel-style migration capability layered over the Database system.
 */
final readonly class Migrations
{
    public function __construct(
        private Querying $querying,
        private Transactions        $transactions
    ) {}

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function builder(string|null $connectionName = null) : QueryBuilder
    {
        return $this->querying->builder(connectionName: $connectionName);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function create(string $table, callable $callback, string|null $connectionName = null) : void
    {
        $this->builder(connectionName: $connectionName)->create(table: $table, callback: $callback);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function dropIfExists(string $table, string|null $connectionName = null) : void
    {
        $this->dropTable(connectionName: $connectionName)->named(table: $table);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function truncate(string $table, string|null $connectionName = null) : void
    {
        $this->truncateTable(connectionName: $connectionName)->named(table: $table);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function createDatabase(string $name, string|null $connectionName = null) : void
    {
        $this->createDatabaseOperation(connectionName: $connectionName)->named(name: $name);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function dropDatabase(string $name, string|null $connectionName = null) : void
    {
        $this->dropDatabaseOperation(connectionName: $connectionName)->named(name: $name);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function repository(string|null $connectionName = null) : MigrationRepository
    {
        return new MigrationRepository(builder: $this->builder(connectionName: $connectionName));
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function runner(string|null $connectionName = null) : MigrationRunner
    {
        return new MigrationRunner(
            repository: $this->repository(connectionName: $connectionName),
            builder   : $this->builder(connectionName: $connectionName)
        );
    }

    public function loader() : MigrationLoader
    {
        return new MigrationLoader();
    }

    public function generator() : MigrationGenerator
    {
        return new MigrationGenerator();
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function exporter(string|null $connectionName = null) : DatabaseExporter
    {
        return new DatabaseExporter(builder: $this->builder(connectionName: $connectionName));
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
            loader    : $this->loader()
        );
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function status(string|null $connectionName = null) : ReadMigrationStatus
    {
        return new ReadMigrationStatus(
            repository: $this->repository(connectionName: $connectionName),
            loader    : $this->loader()
        );
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function createDatabaseOperation(string|null $connectionName = null) : CreateDatabase
    {
        return new CreateDatabase(builder: $this->builder(connectionName: $connectionName));
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function dropDatabaseOperation(string|null $connectionName = null) : DropDatabase
    {
        return new DropDatabase(builder: $this->builder(connectionName: $connectionName));
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function dropTable(string|null $connectionName = null) : DropTable
    {
        return new DropTable(builder: $this->builder(connectionName: $connectionName));
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function truncateTable(string|null $connectionName = null) : TruncateTable
    {
        return new TruncateTable(builder: $this->builder(connectionName: $connectionName));
    }
}
