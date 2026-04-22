<?php

declare(strict_types=1);

namespace Avax\Tests\Core;

use Avax\Database\System\Capabilities\Connections\Connections;
use Avax\Database\System\Capabilities\Migrations\Migrations;
use Avax\Database\System\Capabilities\QueryBuilder\Builder\QueryBuilder;
use Avax\Database\System\Capabilities\QueryBuilder\QueryBuilderRuntime;
use Avax\Database\System\Capabilities\Telemetry\Telemetry;
use Avax\Database\System\Capabilities\Transactions\Transactions;
use Avax\Database\System\Database;
use Avax\Database\System\DatabaseInterface;
use Avax\Tests\TestCase;

class KernelTest extends TestCase
{
    public function test_database_configuration_builds_public_surface() : void
    {
        $database = Database::configuration()->usingConfig([
                                                               'default'     => 'sqlite',
                                                               'connections' => [
                                                                   'sqlite' => [
                                                                       'driver'   => 'sqlite',
                                                                       'database' => ':memory:',
                                                                       'prefix'   => '',
                                                                   ],
                                                               ],
                                                           ])->ready();

        $this->assertInstanceOf(expected: DatabaseInterface::class, actual: $database);
        $this->assertInstanceOf(expected: Connections::class, actual: $database->connections());
        $this->assertInstanceOf(expected: QueryBuilderRuntime::class, actual: $database->query());
        $this->assertInstanceOf(expected: Migrations::class, actual: $database->migrations());
        $this->assertInstanceOf(expected: Transactions::class, actual: $database->transactions());
        $this->assertInstanceOf(expected: Telemetry::class, actual: $database->telemetry());
    }

    public function test_database_capability_aliases_point_to_same_instances() : void
    {
        $this->assertSame(expected: $this->database->queryBuilder(), actual: $this->database->query());
        $this->assertSame(expected: $this->database->migrations(), actual: $this->database->schema());
    }

    public function test_database_table_entrypoint_returns_query_builder() : void
    {
        $builder = $this->database->table(table: 'users');

        $this->assertInstanceOf(expected: QueryBuilder::class, actual: $builder);
    }
}
