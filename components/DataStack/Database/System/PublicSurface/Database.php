<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\PublicSurface;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\Connections;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Migrations as MigrationsCapability;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Schema\Schema as SchemaCapability;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Entities as EntitiesCapability;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Query as QueryCapability;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Telemetry as TelemetryCapability;
use Avax\Components\DataStack\Database\System\Capabilities\Transactions\Transactions as TransactionsCapability;
use Avax\Components\DataStack\Database\System\Configuration\DatabaseBuilder;

/**
 * Public surface for the Database component.
 */
final readonly class Database
{
    public function __construct(
        private Connections $connections,
        private QueryCapability $queryCapability,
        private EntitiesCapability $entitiesCapability,
        private SchemaCapability $schemaCapability,
        private MigrationsCapability $migrationsCapability,
        private TransactionsCapability $transactionsCapability,
        private TelemetryCapability $telemetryCapability,
    ) {}

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
        return new Query(query: $this->queryCapability);
    }

    public function entities(): Entities
    {
        return new Entities(entities: $this->entitiesCapability);
    }

    public function schema(): Schema
    {
        return new Schema(schema: $this->schemaCapability);
    }

    public function migrations(): Migrations
    {
        return new Migrations(migrations: $this->migrationsCapability);
    }

    public function transactions(): Transactions
    {
        return new Transactions(transactions: $this->transactionsCapability);
    }

    public function telemetry(): Telemetry
    {
        return new Telemetry(telemetry: $this->telemetryCapability);
    }

    public function table(string $table, string $connectionName = null) : QueryBuilder
    {
        return $this->queryCapability->from(table: $table, connectionName: $connectionName);
    }
}
