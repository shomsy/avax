<?php

declare(strict_types=1);

namespace Avax\Database\Connection;

use Avax\Database\Connection\Contracts\DatabaseConnection;
use Avax\Database\Connection\Exceptions\ConnectionException;
use Avax\Database\Connection\Exceptions\ConnectionFailure;
use Avax\Database\Foundation\Connection\ConnectionPool;
use PDO;
use Throwable;

/**
 * High-performance technician for managing physical database connections.
 *
 * -- intent: provide a unified entry point for retrieving active database connections.
 */
final class ConnectionManager
{
    // Storage for resolved connection instances
    private array $connections = [];

    /**
     * Constructor promoting the base configuration array.
     *
     * -- intent: store the configuration for lazy connection initialization.
     *
     * @param array $config The database configuration registry
     */
    public function __construct(private readonly array $config) {}

    /**
     * Execute a closure after acquiring a connection from the pool.
     *
     * @param callable    $callback Operation to perform with the pooled connection
     * @param string|null $name     Specific connection identifier
     *
     * @return mixed
     * @throws Throwable If callback or pool acquisition fails
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
     * Start a fluent database operation flow.
     */
    public function flow() : DatabaseFlow
    {
        return new DatabaseFlow(manager: $this);
    }

    /**
     * Retrieve the native PDO instance for a specific connection.
     *
     * -- intent: provide low-level access to the database driver for raw operations.
     *
     * @param string|null $name Technical connection name
     *
     * @return PDO
     * @throws ConnectionException If connection fails
     */
    public function getPdo(string|null $name = null) : PDO
    {
        return $this->connection(name: $name)->getConnection();
    }

    /**
     * Retrieve a connection instance by its technical name.
     *
     * -- intent: resolve connections on-demand and cache them for subsequent use.
     *
     * @param string|null $name Technical identifier of the connection
     *
     * @return DatabaseConnection
     * @throws ConnectionException|ConnectionFailure If the requested configuration is missing or connection fails
     */
    public function connection(string|null $name = null) : DatabaseConnection
    {
        $name = $name ?: $this->getDefaultConnection();

        if (! isset($this->connections[$name])) {
            $this->connections[$name] = $this->makeConnection(name: $name);
        }

        return $this->connections[$name];
    }

    /**
     * Determine the default connection name from the configuration.
     *
     * -- intent: provide a fallback when no specific connection is requested.
     *
     * @return string
     */
    private function getDefaultConnection() : string
    {
        return $this->config['default'] ?? 'mysql';
    }

    /**
     * Create a fresh connection instance based on the technical name.
     *
     * -- intent: resolve the appropriate factory or pool implementation.
     *
     * @param string $name Configuration key for the connection
     *
     * @return DatabaseConnection|ConnectionPool
     */
    private function makeConnection(string $name) : DatabaseConnection|ConnectionPool
    {
        $config = $this->config['connections'][$name] ?? null;

        if ($config === null) {
            throw new ConnectionException(name: $name, message: "Database connection configuration not found.");
        }

        // Ensure the config knows its domain label
        $config['name'] = $name;

        if (isset($config['pool'])) {
            return new ConnectionPool(config: $config);
        }

        return ConnectionFactory::from(config: $config);
    }
}
