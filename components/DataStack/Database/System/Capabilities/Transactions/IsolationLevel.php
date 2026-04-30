<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Transactions;

/**
 * Transaction isolation levels with SQL generation and dialect support.
 *
 * Each level provides the SQL statement to set the isolation level
 * and knows which database dialects support it.
 */
enum IsolationLevel: string
{
    case READ_UNCOMMITTED = 'READ UNCOMMITTED';
    case READ_COMMITTED   = 'READ COMMITTED';
    case REPEATABLE_READ  = 'REPEATABLE READ';
    case SERIALIZABLE     = 'SERIALIZABLE';

    /**
     * Returns the SQL statement to set this isolation level.
     */
    public function toSql(string $dialect = null) : string
    {
        $sql = match ($this) {
            self::READ_UNCOMMITTED => 'SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED',
            self::READ_COMMITTED   => 'SET TRANSACTION ISOLATION LEVEL READ COMMITTED',
            self::REPEATABLE_READ  => 'SET TRANSACTION ISOLATION LEVEL REPEATABLE READ',
            self::SERIALIZABLE     => 'SET TRANSACTION ISOLATION LEVEL SERIALIZABLE',
        };

        return match ($dialect) {
            'mysql'      => $this->mysqlSetIsolationSql(),
            'postgresql' => $this->postgresqlSetIsolationSql(),
            'sqlite'     => $this->sqliteSetIsolationSql(),
            'sqlserver'  => $this->sqlserverSetIsolationSql(),
            default      => $sql,
        };
    }

    /**
     * Returns the MySQL-specific SQL for setting this isolation level.
     */
    private function mysqlSetIsolationSql() : string
    {
        $value = match ($this) {
            self::READ_UNCOMMITTED => 'READ UNCOMMITTED',
            self::READ_COMMITTED   => 'READ COMMITTED',
            self::REPEATABLE_READ  => 'REPEATABLE READ',
            self::SERIALIZABLE     => 'SERIALIZABLE',
        };

        return "SET SESSION TRANSACTION ISOLATION LEVEL {$value}";
    }

    /**
     * Returns the PostgreSQL-specific SQL for setting this isolation level.
     */
    private function postgresqlSetIsolationSql() : string
    {
        $value = match ($this) {
            self::READ_UNCOMMITTED => 'READ UNCOMMITTED',
            self::READ_COMMITTED   => 'READ COMMITTED',
            self::REPEATABLE_READ  => 'REPEATABLE READ',
            self::SERIALIZABLE     => 'SERIALIZABLE',
        };

        return "SET TRANSACTION ISOLATION LEVEL {$value}";
    }

    /**
     * Returns the SQLite-specific SQL for setting this isolation level.
     * SQLite only supports READ UNCOMMITTED (via shared cache) and SERIALIZABLE (default).
     */
    private function sqliteSetIsolationSql() : string
    {
        return match ($this) {
            self::READ_UNCOMMITTED                                          => 'PRAGMA read_uncommitted = true',
            self::READ_COMMITTED, self::REPEATABLE_READ, self::SERIALIZABLE => '',
        };
    }

    /**
     * Returns the SQL Server-specific SQL for setting this isolation level.
     */
    private function sqlserverSetIsolationSql() : string
    {
        return match ($this) {
            self::READ_UNCOMMITTED => 'SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED',
            self::READ_COMMITTED   => 'SET TRANSACTION ISOLATION LEVEL READ COMMITTED',
            self::REPEATABLE_READ  => 'SET TRANSACTION ISOLATION LEVEL REPEATABLE READ',
            self::SERIALIZABLE     => 'SET TRANSACTION ISOLATION LEVEL SERIALIZABLE',
        };
    }

    /**
     * Checks if this isolation level is supported by the given dialect.
     */
    public function supports(string $dialect) : bool
    {
        return match ($dialect) {
            'mysql'      => true,
            'postgresql' => true,
            'sqlite'     => $this === self::READ_COMMITTED || $this === self::SERIALIZABLE || $this === self::READ_UNCOMMITTED,
            'sqlserver'  => true,
            default      => false,
        };
    }

    /**
     * Returns the strictness level (higher = more strict/consistent).
     */
    public function strictness() : int
    {
        return match ($this) {
            self::READ_UNCOMMITTED => 1,
            self::READ_COMMITTED   => 2,
            self::REPEATABLE_READ  => 3,
            self::SERIALIZABLE     => 4,
        };
    }

    /**
     * Checks if this level is stricter than another.
     */
    public function isStricterThan(self $other) : bool
    {
        return $this->strictness() > $other->strictness();
    }

    /**
     * Returns the recommended use case description.
     */
    public function description() : string
    {
        return match ($this) {
            self::READ_UNCOMMITTED => 'Lowest isolation. Allows dirty reads. Use for non-critical analytics.',
            self::READ_COMMITTED   => 'Prevents dirty reads. Default for most databases. Good for general use.',
            self::REPEATABLE_READ  => 'Prevents non-repeatable reads. Default for MySQL/InnoDB. Good for most transactions.',
            self::SERIALIZABLE     => 'Highest isolation. Full serializability. Use when consistency is critical.',
        };
    }
}
