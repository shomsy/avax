<?php
declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\PublicSurface;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\Connections;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Migrations as MigrationsCapability;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Schema\Schema as SchemaCapability;
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
        private Connections             $connections,
        private QueryCapability         $query,
        private SchemaCapability        $schema,
        private MigrationsCapability    $migrations,
        private TransactionsCapability  $transactions,
        private TelemetryCapability     $telemetry
    ) {}

    public static function configuration() : DatabaseBuilder
    {
        return new DatabaseBuilder();
    }

    public function connections() : Connections
    {
        return $this->connections;
    }

    public function query() : Query
    {
        return new Query($this->query);
    }

    public function schema() : Schema
    {
        return new Schema($this->schema);
    }

    public function migrations() : Migrations
    {
        return new Migrations($this->migrations);
    }

    public function transactions() : Transactions
    {
        return new Transactions($this->transactions);
    }

    public function telemetry() : Telemetry
    {
        return new Telemetry($this->telemetry);
    }

    public function table(string $table, ?string $connectionName = null) : QueryBuilder
    {
        return $this->query->from($table, $connectionName);
    }
}