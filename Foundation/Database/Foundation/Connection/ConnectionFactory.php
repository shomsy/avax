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
 * Factory for assembling and validating physical database connections.
 *
 * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/Concepts/Connections.md
 */
final readonly class ConnectionFactory
{
    /**
     * Assemble a validated connection from raw configuration.
     *
     * @param array $config Raw settings dictionary.
     *
     * @return DatabaseConnection
     * @throws ConnectionFailure If assembly or physical link fails.
     */
    public static function from(array $config) : DatabaseConnection
    {
        // First, we convert the raw array into a structured "ConnectionConfig" object.
        // This makes sure we didn't forget any important details like the host or username.
        $config = ConnectionConfig::from(config: $config);

        // Next, we calculate the "DSN" — the technical address the computer uses to call the DB.
        $dsn = Dsn::for(
            driver  : $config->driver,
            host    : $config->host,
            database: $config->database,
            charset : $config->charset
        );

        try {
            // We attempt to open the actual communication line.
            // We force several security/reliability rules here (like ERRMODE_EXCEPTION).
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

            // Once established, we put it inside our own "PdoConnection" wrapper.
            return new PdoConnection(
                name: $config->name,
                pdo : $pdo,
            );
        } catch (Throwable $e) {
            // If anything goes wrong during construction, we wrap the error 
            // so we know exactly which connection name failed.
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
