<?php

declare(strict_types=1);

namespace Avax\Database\System;

use Avax\Database\System\Capabilities\Connections\Connections;
use Avax\Database\System\Capabilities\Migrations\Migrations;
use Avax\Database\System\Capabilities\Migrations\Schema\Schema;
use Avax\Database\System\Capabilities\ORM\EntityManager;
use Avax\Database\System\Capabilities\Query\Builder\QueryBuilder;
use Avax\Database\System\Capabilities\Query\Query;
use Avax\Database\System\Capabilities\Telemetry\Telemetry;
use Avax\Database\System\Capabilities\Transactions\Transactions;

/**
 * Stable public surface for the Database system root.
 */
interface DatabaseInterface
{
    public function connections() : Connections;

    public function query() : Query;

    public function entityManager() : EntityManager;

    public function migrations() : Migrations;

    public function schema() : Schema;

    public function transactions() : Transactions;

    public function telemetry() : Telemetry;

    public function table(string $table, string|null $connectionName = null) : QueryBuilder;
}
