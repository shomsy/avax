<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\Connections;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Migrations;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Schema\Schema;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\EntityManager;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Query;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Telemetry;
use Avax\Components\DataStack\Database\System\Capabilities\Transactions\Transactions;
use Avax\Components\DataStack\Database\System\Configuration\DatabaseBuilder;
use ReflectionException;
use Throwable;

/**
 * Public composition root for the Database system.
 */
final readonly class Database implements DatabaseInterface
{
    public function __construct(
        private Connections $connections,
        private Query $query,
        private EntityManager $entityManager,
        private Schema $schema,
        private Migrations $migrations,
        private Transactions $transactions,
        private Telemetry $telemetry,
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
        return $this->query;
    }

    public function entityManager(): EntityManager
    {
        return $this->entityManager;
    }

    public function migrations(): Migrations
    {
        return $this->migrations;
    }

    public function schema(): Schema
    {
        return $this->schema;
    }

    public function transactions(): Transactions
    {
        return $this->transactions;
    }

    public function telemetry(): Telemetry
    {
        return $this->telemetry;
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
