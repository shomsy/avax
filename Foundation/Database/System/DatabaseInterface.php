<?php

declare(strict_types=1);

namespace Avax\Database\System;

use Avax\Database\System\Capabilities\Connections\Connections;
use Avax\Database\System\Capabilities\Migrations\Migrations;
use Avax\Database\System\Capabilities\Querying\Builder\QueryBuilder;
use Avax\Database\System\Capabilities\Querying\Querying;
use Avax\Database\System\Capabilities\Telemetry\Telemetry;
use Avax\Database\System\Capabilities\Transactions\Transactions;

/**
 * Stable public surface for the Database system root.
 */
interface DatabaseInterface
{
    public function connections() : Connections;

    public function querying() : Querying;

    public function queryBuilder() : Querying;

    public function query() : Querying;

    public function migrations() : Migrations;

    public function schema() : Migrations;

    public function transactions() : Transactions;

    public function telemetry() : Telemetry;

    public function builder(string|null $connectionName = null) : QueryBuilder;

    public function table(string $table, string|null $connectionName = null) : QueryBuilder;
}
