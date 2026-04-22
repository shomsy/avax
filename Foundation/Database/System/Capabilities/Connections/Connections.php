<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Connections;

use Avax\Database\System\Capabilities\Connections\Contracts\DatabaseConnection;
use Avax\Database\System\Capabilities\Telemetry\Support\ExecutionScope;
use PDO;
use Throwable;

/**
 * Public capability owner for database connections and pools.
 */
final readonly class Connections
{
    public function __construct(private ConnectionManager $manager) {}

    /**
     * @throws Throwable
     */
    public function connection(string|null $name = null) : DatabaseConnection
    {
        return $this->manager->connection(name: $name);
    }

    /**
     * @throws Throwable
     */
    public function pdo(string|null $name = null) : PDO
    {
        return $this->manager->getPdo(name: $name);
    }

    /**
     * @throws Throwable
     */
    public function pool(callable $callback, string|null $name = null) : mixed
    {
        return $this->manager->pool(callback: $callback, name: $name);
    }

    public function withScope(ExecutionScope $scope) : self
    {
        return new self(manager: $this->manager->withScope(scope: $scope));
    }

    public function manager() : ConnectionManager
    {
        return $this->manager;
    }
}
