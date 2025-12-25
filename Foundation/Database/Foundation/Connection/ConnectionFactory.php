<?php

declare(strict_types=1);

namespace Avax\Database\Connection;

use Avax\Database\Connection\Contracts\DatabaseConnection;
use Avax\Database\Connection\Exceptions\ConnectionFailure;
use Avax\Database\Connection\ValueObjects\ConnectionConfig;
use Avax\Database\Connection\ValueObjects\Dsn;
use PDO;
use Throwable;

/**
 * Factory: creates DatabaseConnection instances from configuration.
 *
 * Intention:
 * Centralized, declarative, and secure connection assembly with explicit value objects.
 */
final readonly class ConnectionFactory
{
    /**
     * Build a DatabaseConnection from configuration array.
     *
     * @param array{
     *     driver?: string,
     *     host?: string,
     *     database?: string,
     *     username?: string,
     *     password?: string,
     *     charset?: string,
     *     name?: string
     * } $config
     *
     * @throws ConnectionFailure
     */
    public static function from(array $config) : DatabaseConnection
    {
        // Enforce strong typing via ConnectionConfig DTO.
        $config = ConnectionConfig::from(config: $config);

        // Generate DSN as a first-class Value Object.
        $dsn = Dsn::for(
            driver  : $config->driver,
            host    : $config->host,
            database: $config->database,
            charset : $config->charset
        );

        // Attempt secure connection using DTO parameters.
        try {
            $pdo = new PDO(
                dsn     : $dsn->toString(),
                username: $config->username,
                password: $config->password,
                options : [
                              PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                              PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                              PDO::ATTR_EMULATE_PREPARES   => false,
                          ],
            );

            // Return wrapped connection adhering to DatabaseConnection contract.
            return new PdoConnection(
                name: $config->name,
                pdo : $pdo,
            );
        } catch (Throwable $e) {
            // Enterprise-grade exception translation.
            throw new ConnectionFailure(
                name    : $config->name,
                message : sprintf(
                              'Database connection [%s] failed: %s',
                              $config->name,
                              $e->getMessage()
                          ),
                previous: $e
            );
        }
    }
}
