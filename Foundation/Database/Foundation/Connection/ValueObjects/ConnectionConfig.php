<?php

declare(strict_types=1);

namespace Avax\Database\Connection\ValueObjects;

use SensitiveParameter;

/**
 * Immutable value object containing database connection credentials.
 *
 * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/Concepts/Connections.md
 */
final readonly class ConnectionConfig
{
    public string $name;
    public string $charset;
    public string $password;
    public string $username;
    public string $database;
    public string $host;
    public string $driver;

    /**
     * @param string $driver   The type of engine (e.g., 'mysql' or 'sqlite').
     * @param string $host     The "Home Address" (IP or hostname) of the server.
     * @param string $database The specific name of the database file or schema.
     * @param string $username The "User Identity" used to log in.
     * @param string $password The "Secret Key" (Hidden from accidental logging).
     * @param string $charset  The "Language" the database speaks (e.g., utf8).
     * @param string $name     A simple nickname to identify this specific config.
     */
    public function __construct(
        string                       $driver = null,
        string                       $host = null,
        string                       $database = null,
        string                       $username = null,
        #[SensitiveParameter] string $password = null,
        string                       $charset = null,
        string                       $name = 'default',
    )
    {
        $driver         ??= 'mysql';
        $host           ??= '127.0.0.1';
        $database       ??= '';
        $username       ??= 'root';
        $password       ??= '';
        $charset        ??= 'utf8mb4';
        $this->driver   = $driver;
        $this->host     = $host;
        $this->database = $database;
        $this->username = $username;
        $this->password = $password;
        $this->charset  = $charset;
        $this->name     = $name;
    }

    /**
     * Build an ID Card from a raw list of setttings.
     *
     * @param array{
     *     driver?: string,
     *     host?: string,
     *     database?: string,
     *     username?: string,
     *     password?: string,
     *     charset?: string,
     *     name?: string
     * } $config The raw dictionary of details.
     *
     * @return self A fresh, perfectly structured ID Card.
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
