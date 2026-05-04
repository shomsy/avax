<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Transactions;

/**
 * Configuration for the DeadlockDetector.
 */
final readonly class DeadlockDetectorConfig
{
    /**
     * @param list<string> $deadlockSqlStates SQLSTATE codes that indicate deadlocks
     * @param list<string> $deadlockErrorCodes Additional error codes to check
     */
    public function __construct(
        public array $deadlockSqlStates = ['40001', '40P01'],
        public array $deadlockErrorCodes = [],
    )
    {
    }

    /**
     * Creates a configuration with MySQL-specific deadlock codes.
     */
    public static function forMySQL(): self
    {
        return new self(
            deadlockSqlStates: ['40001'],
        );
    }

    /**
     * Creates a configuration with PostgreSQL-specific deadlock codes.
     */
    public static function forPostgreSQL(): self
    {
        return new self(
            deadlockSqlStates: ['40P01', '40001'],
        );
    }

    /**
     * Creates a configuration with SQLite-specific deadlock codes.
     */
    public static function forSQLite(): self
    {
        return new self(
            deadlockSqlStates: ['40001', '5'], // 5 is SQLITE_BUSY
        );
    }
}
