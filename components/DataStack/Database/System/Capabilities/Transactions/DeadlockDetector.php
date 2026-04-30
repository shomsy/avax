<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Transactions;

use PDOException;
use Throwable;

/**
 * Report containing deadlock detection analysis.
 *
 * Readonly value object representing a detected deadlock situation.
 */
final readonly class DeadlockReport
{
    public function __construct(
        public bool  $isDeadlock,
        public string $type = '',
        public string $message = '',
        public string $errorCode = '',
        public string $suggestion = '',
        public array $affectedTables = [],
        public float $detectedAt = 0.0,
    ) {}

    /**
     * Creates a report indicating a detected deadlock.
     */
    public static function deadlock(
        string $type,
        string $message,
        string $errorCode,
        string $suggestion,
        array $affectedTables = [],
    ) : self
    {
        return new self(
            isDeadlock    : true,
            type          : $type,
            message       : $message,
            errorCode     : $errorCode,
            suggestion    : $suggestion,
            affectedTables: $affectedTables,
            detectedAt    : microtime(true),
        );
    }

    /**
     * Creates a report indicating no deadlock was detected.
     */
    public static function notDeadlock() : self
    {
        return new self(
            isDeadlock: false,
            type      : 'none',
            message   : 'No deadlock pattern detected',
            detectedAt: microtime(true),
        );
    }

    /**
     * Returns a human-readable summary of the deadlock report.
     */
    public function summary() : string
    {
        if (! $this->isDeadlock) {
            return 'No deadlock detected';
        }

        $summary = "Deadlock Detected ({$this->type})\n";
        $summary .= "Error Code: {$this->errorCode}\n";
        $summary .= "Message: {$this->message}\n";

        if (! empty($this->affectedTables)) {
            $summary .= 'Affected Tables: ' . implode(', ', $this->affectedTables);
            $summary .= "\n";
        }

        $summary .= "Suggestion: {$this->suggestion}";

        return $summary;
    }
}

/**
 * Detects potential deadlocks from error patterns.
 *
 * This analyzes exception messages and error codes to identify
 * deadlock patterns. It does NOT perform actual database-level
 * deadlock detection (that is the DB driver's responsibility).
 *
 * Uses wait-for graph concepts conceptually to explain deadlock cycles.
 */
final class DeadlockDetector
{
    /**
     * @var list<string> Known SQLSTATE codes that indicate deadlocks
     */
    private const SQLSTATE_DEADLOCKS = ['40001', '40P01', '40001'];

    /**
     * @var list<string> Known error message patterns that indicate deadlocks
     */
    private const DEADLOCK_PATTERNS
        = [
            'deadlock',
            'serialization failure',
            'lock wait timeout',
            'try restarting transaction',
            'lock timeout expired',
            'transaction deadlock',
        ];

    /**
     * @var DeadlockDetectorConfig Configuration for this detector
     */
    private DeadlockDetectorConfig $config;

    public function __construct(DeadlockDetectorConfig $config = null)
    {
        $this->config = $config ?? new DeadlockDetectorConfig();
    }

    /**
     * Analyzes an exception to determine if it represents a deadlock.
     *
     * @return DeadlockReport Analysis result
     */
    public function analyze(Throwable $exception) : DeadlockReport
    {
        $message   = strtolower($exception->getMessage());
        $errorCode = (string) $exception->getCode();
        $sqlState  = $this->extractSqlState($exception);

        // Check SQLSTATE codes first
        if ($this->isDeadlockSqlState($sqlState)) {
            return DeadlockReport::deadlock(
                type          : 'sqlstate_deadlock',
                message       : $exception->getMessage(),
                errorCode     : $sqlState,
                suggestion    : $this->getSuggestionForSqlState($sqlState),
                affectedTables: $this->extractAffectedTables($message),
            );
        }

        // Check error code patterns
        if ($this->isDeadlockErrorCode($errorCode)) {
            return DeadlockReport::deadlock(
                type          : 'error_code_deadlock',
                message       : $exception->getMessage(),
                errorCode     : $errorCode,
                suggestion    : 'Retry the transaction with exponential backoff',
                affectedTables: $this->extractAffectedTables($message),
            );
        }

        // Check message patterns
        foreach (self::DEADLOCK_PATTERNS as $pattern) {
            if (str_contains($message, $pattern)) {
                return DeadlockReport::deadlock(
                    type          : 'message_pattern_deadlock',
                    message       : $exception->getMessage(),
                    errorCode     : $errorCode,
                    suggestion    : $this->getSuggestionForPattern($pattern),
                    affectedTables: $this->extractAffectedTables($message),
                );
            }
        }

        return DeadlockReport::notDeadlock();
    }

    /**
     * Quick check if an exception is a deadlock.
     */
    public function isDeadlock(Throwable $exception) : bool
    {
        return $this->analyze($exception)->isDeadlock;
    }

    /**
     * Analyzes multiple exceptions to find deadlocks.
     *
     * @param list<Throwable> $exceptions
     *
     * @return list<DeadlockReport>
     */
    public function analyzeBatch(array $exceptions) : array
    {
        $reports = [];

        foreach ($exceptions as $exception) {
            $reports[] = $this->analyze($exception);
        }

        return array_values(array_filter(
                                $reports,
                                static fn (DeadlockReport $report) : bool => $report->isDeadlock,
                            ));
    }

    /**
     * Extracts potential table names from an error message.
     *
     * Looks for common patterns like "table `name`", "table name", etc.
     *
     * @return list<string>
     */
    private function extractAffectedTables(string $message) : array
    {
        $tables = [];

        // Pattern: table `name` or table 'name' or table name
        if (preg_match_all("/table\s+[`'\"']?([a-zA-Z_][a-zA-Z0-9_]*)[`'\"']?/", $message, $matches)) {
            $tables = array_merge($tables, $matches[1]);
        }

        // Pattern: on `table_name`
        if (preg_match_all("/on\s+[`'\"']?([a-zA-Z_][a-zA-Z0-9_]*)[`'\"']?/", $message, $matches)) {
            $tables = array_merge($tables, $matches[1]);
        }

        return array_values(array_unique($tables));
    }

    /**
     * Extracts the SQLSTATE from a PDOException.
     */
    private function extractSqlState(Throwable $exception) : string
    {
        if (! $exception instanceof PDOException) {
            return '';
        }

        return $exception->getCode();
    }

    /**
     * Checks if the SQLSTATE indicates a deadlock.
     */
    private function isDeadlockSqlState(string $sqlState) : bool
    {
        if ($sqlState === '') {
            return false;
        }

        return in_array($sqlState, $this->config->deadlockSqlStates, true);
    }

    /**
     * Checks if the error code indicates a deadlock.
     */
    private function isDeadlockErrorCode(string $errorCode) : bool
    {
        if ($errorCode === '') {
            return false;
        }

        return in_array($errorCode, $this->config->deadlockErrorCodes, true);
    }

    /**
     * Gets a suggestion based on the SQLSTATE code.
     */
    private function getSuggestionForSqlState(string $sqlState) : string
    {
        return match ($sqlState) {
            '40001' => 'Serialization failure detected. Retry the transaction. Consider using a lower isolation level or restructuring the transaction order.',
            '40P01' => 'Deadlock detected in PostgreSQL. Retry the transaction. Ensure consistent lock ordering across transactions.',
            default => 'Deadlock detected. Retry the transaction with exponential backoff.',
        };
    }

    /**
     * Gets a suggestion based on the matched error pattern.
     */
    private function getSuggestionForPattern(string $pattern) : string
    {
        return match ($pattern) {
            'deadlock'                   => 'Deadlock detected. Ensure transactions access resources in a consistent order to prevent circular waits.',
            'serialization failure'      => 'Serialization failure. Consider using SERIALIZABLE isolation level or retrying the transaction.',
            'lock wait timeout'          => 'Lock wait timeout. The transaction waited too long for a lock. Check for long-running transactions.',
            'try restarting transaction' => 'Database recommends restarting the transaction. Implement retry logic with backoff.',
            default                      => 'Deadlock-like error detected. Retry the transaction with exponential backoff.',
        };
    }
}

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
    ) {}

    /**
     * Creates a configuration with MySQL-specific deadlock codes.
     */
    public static function forMySQL() : self
    {
        return new self(
            deadlockSqlStates: ['40001'],
        );
    }

    /**
     * Creates a configuration with PostgreSQL-specific deadlock codes.
     */
    public static function forPostgreSQL() : self
    {
        return new self(
            deadlockSqlStates: ['40P01', '40001'],
        );
    }

    /**
     * Creates a configuration with SQLite-specific deadlock codes.
     */
    public static function forSQLite() : self
    {
        return new self(
            deadlockSqlStates: ['40001', '5'], // 5 is SQLITE_BUSY
        );
    }
}
