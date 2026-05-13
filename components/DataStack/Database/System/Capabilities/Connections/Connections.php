<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\Contracts\DatabaseConnection;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\ReadConnection\ReadConnection;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\ReadConnection\ReadPdo;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\RunWithConnection\RunWithConnection;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Trackers\ExecutionScope;
use PDO;
use Throwable;

/**
 * Public capability owner for database connections and pools.
 */
final readonly class Connections
{
    public function __construct(
        private ReadConnection  $readConnection,
        private ReadPdo         $readPdo,
        private RunWithConnection $runWithConnection,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function connection(string|null $name = null) : DatabaseConnection
    {
        return $this->readConnection->connection(name: $name);
    }

    /**
     * @throws Throwable
     */
    public function pdo(string|null $name = null) : PDO
    {
        return $this->readPdo->for(connectionName: $name);
    }

    /**
     * @throws Throwable
     */
    public function pool(callable $callback, string|null $name = null) : mixed
    {
        return $this->runWithConnection->pool(callback: $callback, connectionName: $name);
    }

    public function withScope(ExecutionScope $executionScope): self
    {
        return new self(
            readConnection: $this->readConnection->withScope(executionScope: $executionScope),
            readPdo: $this->readPdo,
            runWithConnection: $this->runWithConnection,
        );
    }
}
