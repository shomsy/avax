<?php

declare(strict_types=1);

namespace components\Database\System;

use components\Database\System\Capabilities\Connections\Connections;
use components\Database\System\Capabilities\Migrations\Migrations;
use components\Database\System\Capabilities\Migrations\Schema\Schema;
use components\Database\System\Capabilities\ORM\EntityManager;
use components\Database\System\Capabilities\Query\Builder\QueryBuilder;
use components\Database\System\Capabilities\Query\Query;
use components\Database\System\Capabilities\Telemetry\Telemetry;
use components\Database\System\Capabilities\Transactions\Transactions;
use components\Database\System\Configuration\DatabaseBuilder;
use ReflectionException;
use Throwable;

/**
 * Public composition root for the Database system.
 */
final readonly class Database implements DatabaseInterface
{
    public function __construct(
        private Connections   $connections,
        private Query         $query,
        private EntityManager $entityManager,
        private Schema        $schema,
        private Migrations    $migrations,
        private Transactions  $transactions,
        private Telemetry     $telemetry
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
        return $this->query;
    }

    public function entityManager() : EntityManager
    {
        return $this->entityManager;
    }

    public function migrations() : Migrations
    {
        return $this->migrations;
    }

    public function schema() : Schema
    {
        return $this->schema;
    }

    public function transactions() : Transactions
    {
        return $this->transactions;
    }

    public function telemetry() : Telemetry
    {
        return $this->telemetry;
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function table(string $table, ?string $connectionName = null) : QueryBuilder
    {
        return $this->query->from(table: $table, connectionName: $connectionName);
    }
}
