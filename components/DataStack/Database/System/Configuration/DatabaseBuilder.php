<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Configuration;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\Connections;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\ReadConnection\ReadConnection;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Migrations;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Schema\Schema;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Query;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Telemetry;
use Avax\Components\DataStack\Database\System\Capabilities\Transactions\Transactions;
use Avax\Components\DataStack\Database\System\PublicSurface\Database;

final class DatabaseBuilder
{
    private array $config = [];

    public function usingConfig(array $config): self
    {
        $this->config = $config;

        return $this;
    }

    public function addConnection(string $name, array $config): self
    {
        $this->config['connections'][$name] = $config;

        return $this;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function ready(): Database
    {
        $connections = new ReadConnection;

        return new Database(
            connections : new Connections($connections),
            query       : new Query,
            schema      : new Schema,
            migrations  : new Migrations,
            transactions: new Transactions,
            telemetry   : new Telemetry,
        );
    }
}
