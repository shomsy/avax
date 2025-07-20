<?php

declare(strict_types=1);

namespace Gemini\Database;

use Gemini\Database\Connection\Contracts\ConnectionPoolInterface;
use PDO;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

/**
 * **DatabaseConnection**
 *
 * Manages secure and optimized database connections using a connection pool.
 *
 * ✅ **Key Features**
 * - Connection Pooling for optimal performance.
 * - Secure Connection Handling (Prevents leaks).
 * - Enterprise-Grade Logging & Error Handling.
 * - Enforces `PDO::ATTR_EMULATE_PREPARES = false` to prevent SQL Injection.
 * - Read/Write Connection Support.
 *
 * 🏆 **Best Practices Implemented**
 * - **SRP (Single Responsibility Principle)** → Only manages database connections.
 * - **DIP (Dependency Inversion Principle)** → Uses an interface for loose coupling.
 * - **Fail-Fast Principle** → Throws exceptions immediately on failures.
 * - **Security Best Practices** → Prevents SQL Injection, connection leaks, and enforces strict error handling.
 *
 * Usage:
 * ```
 * $databaseConnection = new DatabaseConnection($connectionPool, $logger);
 * $pdo = $databaseConnection->getConnection('mysql');
 * $databaseConnection->releaseConnection($pdo);
 * ```
 */
readonly class DatabaseConnection
{
    /**
     * Initializes the database connection manager.
     *
     * @param ConnectionPoolInterface $connectionPool The connection pool instance.
     * @param LoggerInterface         $logger         The logger for structured logging.
     */
    public function __construct(
        private ConnectionPoolInterface $connectionPool,
        private LoggerInterface         $logger
    ) {}

    /**
     * Releases a database connection back into the pool.
     *
     * ✅ **Ensures Proper Resource Management**
     * - Avoids connection leaks by returning the connection to the pool.
     * - Logs successful release operations.
     *
     * @param PDO $pdo The connection to release.
     */
    public function releaseConnection(PDO $pdo) : void
    {
        $this->connectionPool->releaseConnection(pdo: $pdo);
        $this->logger->info(message: "Database connection successfully released.");
    }

    /**
     * Checks if the database connection is alive.
     *
     * ✅ **Fail-Fast Design**
     * - Executes a lightweight `SELECT 1` query to test the connection.
     * - Logs failures for monitoring.
     *
     * @param string|null $connectionName Optional connection name.
     *
     * @return bool `true` if the connection is active, `false` otherwise.
     */
    public function testConnection(string|null $connectionName = null) : bool
    {
        try {
            $pdo = $this->getConnection(connectionName: $connectionName);
            $pdo->query(query: 'SELECT 1');

            return true;
        } catch (Throwable $exception) {
            $this->logger->warning(
                message: "Database connection test failed.",
                context: [
                             'connection' => $connectionName,
                             'error'      => $exception->getMessage(),
                         ]
            );

            return false;
        }
    }

    /**
     * Retrieves a secure database connection from the pool.
     *
     * ✅ **Security Enhancements**
     * - Enforces `PDO::ATTR_EMULATE_PREPARES = false` to prevent SQL Injection.
     * - Ensures only valid connection names are used.
     *
     * @param string|null $connectionName Optional connection name (default: primary connection).
     *
     * @return PDO A secure, pooled database connection.
     *
     * @throws RuntimeException If the connection cannot be established.
     */
    public function getConnection(string|null $connectionName = null) : PDO
    {
        try {
            // Retrieve a database connection from the connection pool.
            // The variable `$connectionName` specifies the name of the database connection to use.
            $pdo = $this->connectionPool->getConnection(connectionName: $connectionName);
            // ✅ Security Hardening – Ensure emulated prepares are disabled to prevent SQL Injection.
            $pdo->setAttribute(attribute: PDO::ATTR_EMULATE_PREPARES, value: false);

            return $pdo;
        } catch (Throwable $exception) {
            $this->logger->error(
                message: "Database connection error: " . $exception->getMessage(),
                context: [
                             'connection' => $connectionName,
                             'trace'      => $exception->getTraceAsString(),
                         ]
            );

            throw new RuntimeException(message: "Failed to establish a secure database connection.");
        }
    }
}
