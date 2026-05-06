<?php

declare(strict_types=1);

namespace components\Database;

use components\Database\System\Capabilities\Connections\Connections;
use components\Database\System\Capabilities\Migrations\Migrations as MigrationsCapability;
use components\Database\System\Capabilities\Migrations\Schema\Schema as SchemaCapability;
use components\Database\System\Capabilities\ORM\EntityManager as EntityManagerCapability;
use components\Database\System\Capabilities\Query\Builder\QueryBuilder;
use components\Database\System\Capabilities\Query\Query as QueryCapability;
use components\Database\System\Capabilities\Telemetry\Telemetry as TelemetryCapability;
use components\Database\System\Capabilities\Transactions\Transactions as TransactionsCapability;
use components\Database\System\Configuration\DatabaseBuilder;
use ReflectionException;
use Throwable;

final readonly class Database
{
    public function __construct(
        private Connections $connections,
        private QueryCapability $query,
        private EntityManagerCapability $entityManager,
        private SchemaCapability $schema,
        private MigrationsCapability $migrations,
        private TransactionsCapability $transactions,
        private TelemetryCapability $telemetry
    ) {
    }

    public static function configuration(): DatabaseBuilder
    {
        return new DatabaseBuilder();
    }

    public function connections(): Connections
    {
        return $this->connections;
    }

    public function query(): Query
    {
        return new Query(query: $this->query);
    }

    public function entityManager(): EntityManager
    {
        return new EntityManager(entityManager: $this->entityManager);
    }

    public function schema(): Schema
    {
        return new Schema(schema: $this->schema);
    }

    public function migrations(): Migrations
    {
        return new Migrations(migrations: $this->migrations);
    }

    public function transactions(): Transactions
    {
        return new Transactions(transactions: $this->transactions);
    }

    public function telemetry(): Telemetry
    {
        return new Telemetry(telemetry: $this->telemetry);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function table(string $table, ?string $connectionName = null): QueryBuilder
    {
        return $this->query->from(table: $table, connectionName: $connectionName);
    }
}
