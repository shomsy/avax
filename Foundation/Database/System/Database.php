<?php

declare(strict_types=1);

namespace Avax\Database\System;

use Avax\Database\System\Capabilities\Connections\Connections;
use Avax\Database\System\Capabilities\Migrations\Migrations;
use Avax\Database\System\Capabilities\QueryBuilder\Builder\QueryBuilder;
use Avax\Database\System\Capabilities\QueryBuilder\QueryBuilderRuntime;
use Avax\Database\System\Capabilities\Telemetry\Telemetry;
use Avax\Database\System\Capabilities\Transactions\Transactions;
use Avax\Database\System\Configuration\DatabaseBuilder;

/**
 * Public composition root for the Database system.
 */
final readonly class Database implements DatabaseInterface
{
    public function __construct(
        private Connections         $connections,
        private QueryBuilderRuntime $queryBuilder,
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

    public function queryBuilder() : QueryBuilderRuntime
    {
        return $this->queryBuilder;
    }

    public function query() : QueryBuilderRuntime
    {
        return $this->queryBuilder();
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

    public function builder(string|null $connectionName = null) : QueryBuilder
    {
        return $this->queryBuilder->builder(connectionName: $connectionName);
    }

    public function table(string $table, string|null $connectionName = null) : QueryBuilder
    {
        return $this->queryBuilder->from(table: $table, connectionName: $connectionName);
    }
}
