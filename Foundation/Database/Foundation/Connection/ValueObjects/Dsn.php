<?php

declare(strict_types=1);

namespace Avax\Database\Connection\ValueObjects;

/**
 * Immutable value object encapsulating a PDO Data Source Name string.
 *
 * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/Concepts/Connections.md
 */
final readonly class Dsn
{
    /**
     * Private constructor — use `Dsn::for()` to create one.
     *
     * @param string $dsn The final, "Computer-Ready" address string.
     */
    private function __construct(private string $dsn) {}

    /**
     * Build a technical address from simple settings.
     *
     * @param string $driver   The type of database (e.g., 'mysql', 'sqlite').
     * @param string $host     The computer's address (e.g., '127.0.0.1').
     * @param string $database The name of the specific database (e.g., 'users_db').
     * @param string $charset  The "Language" (Encoding) to use (e.g., 'utf8').
     *
     * @return self An immutable object holding the perfectly formatted address.
     */
    public static function for(
        string $driver,
        string $host,
        string $database,
        string $charset,
    ) : self
    {
        // Different drivers have different "Address Formats".
        // SQLite:   "sqlite:/path/to/db.sqlite"
        // MySQL:    "mysql:host=127.0.0.1;dbname=test;charset=utf8"
        $dsn = match ($driver) {
            'sqlite' => sprintf('sqlite:%s', $database),
            default  => sprintf('%s:host=%s;dbname=%s;charset=%s', $driver, $host, $database, $charset),
        };

        return new self(dsn: $dsn);
    }

    /**
     * Get the compiled DSN string for PDO.
     *
     * @return string
     */
    public function toString() : string
    {
        return $this->dsn;
    }
}
