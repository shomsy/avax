<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections\ValueObjects;

use InvalidArgumentException;

/**
 * Canonical database drivers as a backed enum.
 *
 * Replaces bare string literals like 'mysql', 'sqlite', etc.
 * with type-safe enum cases.
 */
enum DatabaseDriver: string
{
    case MySQL = 'mysql';
    case SQLite = 'sqlite';
    case PostgreSQL = 'pgsql';
    case SQLServer = 'sqlsrv';

    /**
     * Create a DatabaseDriver from a string value.
     *
     * @throws InvalidArgumentException if the driver is not recognized.
     */
    public static function fromString(string $driver): self
    {
        return match (strtolower($driver)) {
            'mysql' => self::MySQL,
            'sqlite' => self::SQLite,
            'pgsql', 'postgresql' => self::PostgreSQL,
            'sqlsrv', 'mssql' => self::SQLServer,
            default => throw new InvalidArgumentException("Unknown database driver: {$driver}"),
        };
    }

    /**
     * Try to create a DatabaseDriver from a string, returning null if unknown.
     */
    public static function tryFromString(string $driver) : self|null
    {
        return match (strtolower($driver)) {
            'mysql' => self::MySQL,
            'sqlite' => self::SQLite,
            'pgsql', 'postgresql' => self::PostgreSQL,
            'sqlsrv', 'mssql' => self::SQLServer,
            default => null,
        };
    }
}
