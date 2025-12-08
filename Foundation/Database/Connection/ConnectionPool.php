<?php

declare(strict_types=1);

namespace Avax\Database\Connection;

use Exception;
use Avax\Database\Connection\Contracts\ConnectionPoolInterface;
use PDO;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * Class ConnectionPool
 *
 * Manages a pool of PDO connections to improve efficiency and reuse within an application.
 * Implements the ConnectionPoolInterface to allow for consistent connection handling.
 *
 * Features:
 * - Supports named database connections (`mysql`, `pgsql`, `sqlite`, `sqlsrv`).
 * - Automatically initializes and pools connections.
 * - Efficiently reuses available connections.
 * - Ensures that connections are alive before reuse.
 * - Implements exception handling and logging.
 */
class ConnectionPool implements ConnectionPoolInterface
{
    /** @var array<string, PDO> Active connections stored by name. */
    private array $connections = [];

    /** @var array<string, array> Configuration settings for all connections. */
    private array           $config;

    private LoggerInterface $logger;

    private int             $maxConnections;

    /**
     * ConnectionPool constructor.
     *
     * @param array           $config         Database configuration array.
     * @param LoggerInterface $logger         Logger for handling errors and connection issues.
     * @param int             $maxConnections Maximum number of connections allowed per database.
     */
    public function __construct(
        array           $config,
        LoggerInterface $logger,
        int             $maxConnections = 5
    ) {
        $this->config         = $config['connections'] ?? [];
        $this->logger         = $logger;
        $this->maxConnections = $maxConnections;
    }

    /**
     * Retrieves an available database connection by name.
     *
     * @param string|null $connectionName The database connection to retrieve.
     *
     * @return PDO The active PDO connection.
     *
     * @throws RuntimeException If the requested connection is not configured.
     */
    public function getConnection(string|null $connectionName = null) : PDO
    {
        $connectionName ??= config(key: 'database.default', default: 'mysql');

        if (! isset($this->config[$connectionName])) {
            throw new RuntimeException(message: "Database connection '{$connectionName}' is not configured.");
        }

        if (isset($this->connections[$connectionName])
            && $this->isConnectionAvailable(pdo: $this->connections[$connectionName])
        ) {
            return $this->connections[$connectionName];
        }

        if (count($this->connections) < $this->maxConnections) {
            return $this->initializeConnection(connectionName: $connectionName);
        }

        throw new RuntimeException(message: "Connection pool limit reached for '{$connectionName}'.");
    }

    /**
     * Checks if the given PDO connection is still available and functional.
     *
     * @param PDO $pdo The PDO connection instance to check.
     *
     * @return bool Returns true if the connection is valid, false otherwise.
     */
    private function isConnectionAvailable(PDO $pdo) : bool
    {
        try {
            return $pdo->query(query: 'SELECT 1') !== false;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Initializes a new database connection.
     *
     * @param string $connectionName The name of the database connection.
     *
     * @return PDO The newly created PDO connection.
     *
     * @throws RuntimeException If connection fails.
     */
    private function initializeConnection(string $connectionName) : PDO
    {
        try {
            $config = $this->config[$connectionName];

            $pdo = new PDO(
                dsn     : $config['connection'],
                username: $config['username'] ?? null,
                password: $config['password'] ?? null,
                options : $config['options'] ?? []
            );

            $pdo->setAttribute(attribute: PDO::ATTR_ERRMODE, value: PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(attribute: PDO::ATTR_EMULATE_PREPARES, value: false);

            $this->connections[$connectionName] = $pdo;

            $this->logger->info(message: "Successfully connected to '{$connectionName}' database.");

            return $pdo;
        } catch (Exception $e) {
            $this->logger->error(message: "Failed to connect to '{$connectionName}': {$e->getMessage()}");
            throw new RuntimeException(message: "Database connection failed: {$e->getMessage()}", previous: $e);
        }
    }

    /**
     * Releases a connection back into the pool.
     *
     * @param PDO $pdo The PDO connection to release.
     */
    public function releaseConnection(PDO $pdo) : void
    {
        foreach ($this->connections as $name => $connection) {
            if ($connection === $pdo) {
                $this->logger->info(message: "Releasing connection for '{$name}' back to the pool.");

                return;
            }
        }

        $this->logger->warning(message: 'Attempted to release an unknown connection.');
    }
}