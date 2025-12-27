<?php

declare(strict_types=1);

namespace Avax\Database\Connection;

use Avax\Database\Connection\Contracts\DatabaseConnection;
use Avax\Database\Connection\Exceptions\ConnectionException;
use Avax\Database\Events\EventBus;
use Avax\Database\Foundation\Connection\Pool\ConnectionPool;
use Avax\Database\Foundation\Connection\Pool\PooledConnectionAuthority;
use Avax\Database\Support\ExecutionScope;
use PDO;
use Random\RandomException;
use Throwable;

/**
 * Central registry and lifecycle manager for all database connections and pools.
 *
 * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/Concepts/Connections.md
 */
final class ConnectionManager
{
    /** @var array<string, DatabaseConnection> A memory bank for connections we've already opened. */
    private array $connections = [];

    /** @var array<string, ConnectionPool> A memory bank for "Shared Library" (Pool) setups. */
    private array $pools = [];

    /**
     * @param array<string, mixed> $config   The master Settings Book containing details for all connections.
     * @param EventBus|null        $eventBus The "Notification System" for reporting when connections open or fail.
     * @param ExecutionScope|null  $scope    The "Passenger Ticket" that links this manager to a specific request.
     *
     * @throws RandomException
     */
    public function __construct(
        private readonly array         $config,
        private readonly EventBus|null $eventBus = null,
        private ExecutionScope|null    $scope = null
    )
    {
        $this->scope ??= ExecutionScope::fresh();
    }

    /**
     * Execute a task using a borrowed connection from the pool.
     *
     * @param callable    $callback Task to run.
     * @param string|null $name     Specific connection name.
     *
     * @return mixed
     */
    public function pool(callable $callback, string|null $name = null) : mixed
    {
        $connection = $this->flow()->usePool();
        if ($name) {
            $connection->on(name: $name);
        }

        return $connection->run(callback: $callback);
    }

    /**
     * Initiate a fluent connection construction journey.
     *
     * @return DatabaseFlow
     */
    public function flow() : DatabaseFlow
    {
        return new DatabaseFlow(manager: $this);
    }

    /**
     * Get the raw technical tool (PDO) for a specific database.
     *
     * @param string|null $name The nickname (e.g., 'primary').
     *
     * @return PDO The raw technical engine.
     */
    public function getPdo(string|null $name = null) : PDO
    {
        return $this->connection(name: $name)->getConnection();
    }

    /**
     * Retrieve a cached or new connection by nickname.
     *
     * @param string|null $name Connection nickname (null for default).
     *
     * @return DatabaseConnection
     */
    public function connection(string|null $name = null) : DatabaseConnection
    {
        $name = $name ?: $this->getDefaultConnection();

        if (isset($this->connections[$name])) {
            return $this->connections[$name];
        }

        $connection = $this->makeConnection(name: $name);

        // We don't cache pooled authorities here because they handle their own 
        // internal caching/lazy-loading logic via the Pool.
        if ($connection instanceof PooledConnectionAuthority) {
            return $connection;
        }

        $this->connections[$name] = $connection;

        return $connection;
    }

    /**
     * Find out which connection is set as the "Main" one.
     */
    private function getDefaultConnection() : string
    {
        return $this->config['default'] ?? 'mysql';
    }

    /**
     * The internal logic for building a connection from scratch.
     *
     * @throws Throwable
     */
    private function makeConnection(string $name) : DatabaseConnection
    {
        $config = $this->config['connections'][$name] ?? null;

        if ($config === null) {
            throw new ConnectionException(name: $name, message: "Database connection configuration not found.");
        }

        $config['name'] = $name;

        // If the settings include a 'pool' section, we create a shared library pool.
        if (isset($config['pool'])) {
            if (! isset($this->pools[$name])) {
                $this->pools[$name] = new ConnectionPool(
                    config  : $config,
                    eventBus: $this->eventBus
                );
            }

            $pool = $this->pools[$name];
            if ($this->scope) {
                $pool->withScope(scope: $this->scope);
            }

            return new PooledConnectionAuthority(pool: $pool);
        }

        // Otherwise, we just open a direct, private line to the database.
        $flow = DirectConnectionFlow::begin()->using(config: $config);

        if ($this->eventBus) {
            $flow->withEvents(eventBus: $this->eventBus);
        }

        if ($this->scope) {
            $flow->withScope(scope: $this->scope);
        }

        return $flow->connect();
    }

    /**
     * Create a clone with a specific correlation scope.
     *
     * @param ExecutionScope $scope Context for telemetry.
     *
     * @return self
     */
    public function withScope(ExecutionScope $scope) : self
    {
        $clone        = clone $this;
        $clone->scope = $scope;

        return $clone;
    }
}
