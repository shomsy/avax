<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Database\Core;

use Avax\Database\Database;
use Avax\Database\EntityManager;
use Avax\Database\Migrations;
use Avax\Database\Query;
use Avax\Database\Schema;
use Avax\Database\System\Capabilities\Connections\Connections;
use Avax\Database\System\Capabilities\Query\Builder\QueryBuilder;
use Avax\Database\Telemetry;
use Avax\Database\Transactions;
use Avax\Tests\TestCase;
use Random\RandomException;
use ReflectionException;
use Throwable;

class KernelTest extends TestCase
{
    /**
     * @throws RandomException
     */
    public function test_database_configuration_builds_public_surface() : void
    {
        $database = Database::configuration()->usingConfig(config: [
                                                               'default'     => 'sqlite',
                                                               'connections' => [
                                                                   'sqlite' => [
                                                                       'driver'   => 'sqlite',
                                                                       'database' => ':memory:',
                                                                       'prefix'   => '',
                                                                   ],
                                                               ],
                                                           ])->ready();

        $this->assertInstanceOf(expected: Database::class, actual: $database);
        $this->assertInstanceOf(expected: Connections::class, actual: $database->connections());
        $this->assertInstanceOf(expected: Query::class, actual: $database->query());
        $this->assertInstanceOf(expected: EntityManager::class, actual: $database->entityManager());
        $this->assertInstanceOf(expected: Schema::class, actual: $database->schema());
        $this->assertInstanceOf(expected: Migrations::class, actual: $database->migrations());
        $this->assertInstanceOf(expected: Transactions::class, actual: $database->transactions());
        $this->assertInstanceOf(expected: Telemetry::class, actual: $database->telemetry());
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function test_database_table_entrypoint_returns_query_builder() : void
    {
        $builder = $this->database->table(table: 'users');

        $this->assertInstanceOf(expected: QueryBuilder::class, actual: $builder);
    }
}
