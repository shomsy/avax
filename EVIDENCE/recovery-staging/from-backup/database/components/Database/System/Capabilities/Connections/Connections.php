<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Connections;

use Avax\Database\System\Capabilities\Connections\Contracts\DatabaseConnection;
use Avax\Database\System\Capabilities\Connections\ReadConnection\ReadConnection;
use Avax\Database\System\Capabilities\Connections\ReadConnection\ReadPdo;
use Avax\Database\System\Capabilities\Connections\RunWithConnection\RunWithConnection;
use Avax\Database\System\Capabilities\Telemetry\Support\ExecutionScope;
use PDO;
use Throwable;

/**
 * Public capability owner for database connections and pools.
 */
final readonly class Connections
{
    private ReadPdo $readPdo;

    private RunWithConnection $runWithConnection;

    public function __construct(
        private ReadConnection $readConnection,
        ?ReadPdo $readPdo = null,
        ?RunWithConnection $runWithConnection = null
    ) {
        $this->readPdo = $readPdo ?? new ReadPdo(readConnection: $this->readConnection);
        $this->runWithConnection = $runWithConnection ?? new RunWithConnection(readConnection: $this->readConnection);
    }

    /**
     * @throws Throwable
     */
    public function connection(?string $name = null): DatabaseConnection
    {
        return $this->readConnection->connection(name: $name);
    }

    /**
     * @throws Throwable
     */
    public function pdo(?string $name = null): PDO
    {
        return $this->readPdo->for(connectionName: $name);
    }

    /**
     * @throws Throwable
     */
    public function pool(callable $callback, ?string $name = null): mixed
    {
        return $this->runWithConnection->pool(callback: $callback, connectionName: $name);
    }

    public function withScope(ExecutionScope $scope): self
    {
        return new self(readConnection: $this->readConnection->withScope(scope: $scope));
    }
}
