<?php

declare(strict_types=1);

namespace Avax\Database\System;

use Avax\Database\System\Capabilities\Connections\Connections;
use Avax\Database\System\Capabilities\Migrations\Migrations;
use Avax\Database\System\Capabilities\Querying\Builder\QueryBuilder;
use Avax\Database\System\Capabilities\Querying\Querying;
use Avax\Database\System\Capabilities\Telemetry\Telemetry;
use Avax\Database\System\Capabilities\Transactions\Transactions;
use Avax\Database\System\Configuration\DatabaseBuilder;
use ReflectionException;
use Throwable;

/**
 * Public composition root for the Database system.
 */
final readonly class Database implements DatabaseInterface
{
    public function __construct(
        private Connections         $connections,
        private Querying $querying,
        private Migrations          $migrations,
        private Transactions        $transactions,
        private Telemetry           $telemetry
    ) {}

    public static function configuration() : DatabaseBuilder
    {
        return new DatabaseBuilder();
    }

    public function connections() : Connections
    {
        return $this->connections;
    }

    public function querying() : Querying
    {
        return $this->querying;
    }

    public function queryBuilder() : Querying
    {
        return $this->querying();
    }

    public function query() : Querying
    {
        return $this->querying();
    }

    public function migrations() : Migrations
    {
        return $this->migrations;
    }

    public function schema() : Migrations
    {
        return $this->migrations();
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
    public function builder(string|null $connectionName = null) : QueryBuilder
    {
        return $this->querying->builder(connectionName: $connectionName);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function table(string $table, string|null $connectionName = null) : QueryBuilder
    {
        return $this->querying->from(table: $table, connectionName: $connectionName);
    }
}
