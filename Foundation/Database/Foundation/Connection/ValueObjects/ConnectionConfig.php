<?php

declare(strict_types=1);

namespace Avax\Database\Connection\ValueObjects;

use SensitiveParameter;

/**
 * Immutable value object representing a database connection's technical configuration.
 *
 * -- intent: centralize and type-hint the varied parameters required to establish a connection.
 * -- design: leverage PHP 8.3 readonly features for strict data integrity.
 */
final readonly class ConnectionConfig
{
    /**
     * Constructor using promoted properties for concise parameter capturing.
     *
     * @param string $driver   Database engine driver (e.g., 'mysql', 'pgsql')
     * @param string $host     Network address of the database host
     * @param string $database Name of the target schema/database
     * @param string $username Security credential (user identifier)
     * @param string $password Security credential (secret token)
     * @param string $charset  Character encoding protocol
     * @param string $name     Logical identifier for the connection within the system
     */
    public function __construct(
        public string                       $driver = 'mysql',
        public string                       $host = '127.0.0.1',
        public string                       $database = '',
        public string                       $username = 'root',
        #[SensitiveParameter] public string $password = '',
        public string                       $charset = 'utf8mb4',
        public string                       $name = 'default',
    ) {}

    /**
     * Static factory for hydrating the config from a raw associative array.
     *
     * @param array $config Key-value map of configuration settings
     *
     * @return self
     */
    public static function from(array $config) : self
    {
        return new self(
            driver  : $config['driver'] ?? 'mysql',
            host    : $config['host'] ?? '127.0.0.1',
            database: $config['database'] ?? '',
            username: $config['username'] ?? 'root',
            password: $config['password'] ?? '',
            charset : $config['charset'] ?? 'utf8mb4',
            name    : $config['name'] ?? 'default'
        );
    }
}
