<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Migrations;

use Avax\Database\System\Capabilities\Migrations\Execution\Repository\MigrationRepository;
use Avax\Database\System\Capabilities\Migrations\Execution\Runner\MigrationRunner;
use Avax\Database\System\Capabilities\Migrations\Export\DatabaseExporter;
use Avax\Database\System\Capabilities\Migrations\Generate\MigrationGenerator;
use Avax\Database\System\Capabilities\Migrations\Generate\MigrationLoader;
use Avax\Database\System\Capabilities\QueryBuilder\Builder\QueryBuilder;
use Avax\Database\System\Capabilities\QueryBuilder\QueryBuilderRuntime;
use Avax\Database\System\Capabilities\Transactions\Transactions;
use ReflectionException;
use Throwable;

/**
 * Laravel-style migration capability layered over the Database system.
 */
final readonly class Migrations
{
    public function __construct(
        private QueryBuilderRuntime $queryBuilder,
        private Transactions        $transactions
    ) {}

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function builder(string|null $connectionName = null) : QueryBuilder
    {
        return $this->queryBuilder->builder(connectionName: $connectionName);
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
        $this->builder(connectionName: $connectionName)->dropIfExists(table: $table);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function truncate(string $table, string|null $connectionName = null) : void
    {
        $this->builder(connectionName: $connectionName)->truncate(table: $table);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function createDatabase(string $name, string|null $connectionName = null) : void
    {
        $this->builder(connectionName: $connectionName)->createDatabase(name: $name);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function dropDatabase(string $name, string|null $connectionName = null) : void
    {
        $this->builder(connectionName: $connectionName)->dropDatabase(name: $name);
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
}
