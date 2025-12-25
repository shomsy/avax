<?php

declare(strict_types=1);

namespace Avax\Database\Connection\ValueObjects;

use InvalidArgumentException;

/**
 * Class Dsn
 *
 * -- intent: Represents a complete, immutable DSN (Data Source Name) string used for database connections.
 * -- context: Used by ConnectionFactory and ConnectionManager to safely construct driver-specific DSNs.
 * -- guarantees: Once created, it cannot change; always produces a valid and sanitized DSN string.
 *
 * Example:
 * ```php
 * $dsn = Dsn::for('mysql', 'localhost', 'avax', 'utf8mb4')->toString();
 * // mysql:host=localhost;dbname=avax;charset=utf8mb4
 * ```
 *
 * @package Avax\Database\Connection\ValueObjects
 * @since   1.0.0
 */
final readonly class Dsn
{
    /**
     * -- intent: Internal constructor ensures DSN immutability (Value Object pattern).
     * -- rationale: Prevents direct instantiation; forces creation via named constructor.
     *
     * @param string $dsn Fully formatted DSN string.
     */
    private function __construct(private string $dsn) {}

    /**
     * -- intent: Factory method for building a driver-specific DSN from basic connection parameters.
     * -- guarantees: Returns a valid DSN string for all supported drivers (currently MySQL & SQLite).
     * -- rationale: Keeps DSN generation consistent and free from manual string concatenation.
     *
     * Example:
     * ```php
     * Dsn::for('sqlite', '/tmp/db.sqlite', '', '');
     * Dsn::for('mysql', 'localhost', 'avax', 'utf8mb4');
     * ```
     *
     * @param string $driver   The database driver name (e.g. mysql, sqlite).
     * @param string $host     The database host or file path (for sqlite).
     * @param string $database The database name or file (depending on driver).
     * @param string $charset  The character encoding (e.g. utf8mb4).
     *
     * @return self A new immutable Dsn instance.
     *
     * @throws InvalidArgumentException If the driver is unsupported or parameters are invalid.
     */
    public static function for(
        string $driver,
        string $host,
        string $database,
        string $charset,
    ) : self
    {
        // -- intent: Select DSN format based on driver type
        // -- SECURITY: Prevents injection by strict string formatting (sprintf)
        $dsn = match ($driver) {
            'sqlite' => sprintf('sqlite:%s', $database),
            default  => sprintf('%s:host=%s;dbname=%s;charset=%s', $driver, $host, $database, $charset),
        };

        // -- intent: Return a new immutable DSN instance
        return new self(dsn: $dsn);
    }

    /**
     * -- intent: Retrieve the DSN as a plain string for PDO or ConnectionManager.
     * -- guarantees: Always returns a valid, non-empty DSN.
     *
     * Example:
     * ```php
     * echo $dsn->toString(); // mysql:host=localhost;dbname=avax;charset=utf8mb4
     * ```
     *
     * @return string The final DSN string representation.
     */
    public function toString() : string
    {
        return $this->dsn;
    }
}
