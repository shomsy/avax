<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections\ConnectionTypes;

use SensitiveParameter;

/**
 * Immutable value object containing database connection credentials.
 *
 * @see /docs/Foundation/Database/Concepts/Connections.md
 */
final readonly class ConnectionConfig
{
    public string $charset;

    public string $password;

    public string $username;

    public string $database;

    public string $host;

    public DatabaseDriver $driver;

    /**
     * @param  DatabaseDriver|string  $driver  The type of engine (e.g., DatabaseDriver::MySQL or 'mysql').
     * @param  string  $host  The "Home Address" (IP or hostname) of the server.
     * @param  string  $database  The specific name of the database file or schema.
     * @param  string  $username  The "User Identity" used to log in.
     * @param  string  $password  The "Secret Key" (Hidden from accidental logging).
     * @param  string  $charset  The "Language" the database speaks (e.g., utf8).
     * @param  string  $name  A simple nickname to identify this specific config.
     */
    public function __construct(
        DatabaseDriver|string|null $driver = null, string|null $host = null, string|null $database = null, string|null $username = null,
        #[SensitiveParameter]
        ?string                    $password = null, string|null $charset = null,
        public string $name = 'default',
    ) {
        $driver = is_string($driver) ? DatabaseDriver::fromString($driver) : ($driver ?? DatabaseDriver::MySQL);
        $host ??= '127.0.0.1';
        $database ??= '';
        $username ??= 'root';
        $password ??= '';
        $charset ??= 'utf8mb4';
        $this->driver = $driver;
        $this->host = $host;
        $this->database = $database;
        $this->username = $username;
        $this->password = $password;
        $this->charset = $charset;
    }

    /**
     * Build an ID Card from a raw list of setttings.
     *
     * @param array{
     *     driver?: DatabaseDriver|string,
     *     host?: string,
     *     database?: string,
     *     username?: string,
     *     password?: string,
     *     charset?: string,
     *     name?: string
     * } $config The raw dictionary of details.
     * @return self A fresh, perfectly structured ID Card.
     */
    public static function from(array $config): self
    {
        $driver = $config['driver'] ?? DatabaseDriver::MySQL;

        return new self(
            driver  : $driver,
            host    : $config['host'] ?? '127.0.0.1',
            database: $config['database'] ?? '',
            username: $config['username'] ?? 'root',
            password: $config['password'] ?? '',
            charset : $config['charset'] ?? 'utf8mb4',
            name    : $config['name'] ?? 'default',
        );
    }
}
