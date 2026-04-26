<?php

declare(strict_types=1);

namespace Avax\DataLayer\AccessPersistentData;

use Exception;
use InvalidArgumentException;
use PDO;
use Throwable;

final readonly class RunDataTransaction
{
    private const DEFAULT_ISOLATION = PDO::TRANSACTION_REPEATABLE_READ;
    private const DEFAULT_TIMEOUT   = 60;

    public function __construct(private UseDatabaseRuntime $useDatabaseRuntime) {}

    public function runWithIsolation(
        callable    $callback,
        int|null    $isolationLevel = null,
        string|null $connectionName = null
    ) : mixed
    {
        $isolationLevel    ??= self::DEFAULT_ISOLATION;
        $pdo               = $this->useDatabaseRuntime->connection(name: $connectionName);
        $previousIsolation = $this->getCurrentIsolation(connectionName: $pdo);

        try {
            $pdo->exec(statement: sprintf('SET TRANSACTION ISOLATION LEVEL %s', $this->isolationLevelToSql(level: $isolationLevel)));
            $pdo->beginTransaction();

            $result = $callback($pdo);

            if ($pdo->inTransaction()) {
                $pdo->commit();
            }

            return $result;

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            if ($previousIsolation !== null) {
                try {
                    $pdo->exec(statement: sprintf('SET TRANSACTION ISOLATION LEVEL %s', $this->isolationLevelToSql(level: $previousIsolation)));
                } catch (Throwable) {
                }
            }

            throw new PersistentDataFailure(
                message : sprintf('Transaction with isolation %s failed: %s', $this->isolationLevelToSql(level: $isolationLevel), $e->getMessage()),
                code    : $e->getCode(),
                previous: $e
            );
        }
    }

    public function getCurrentIsolation(string|null $connectionName = null) : int|null
    {
        $pdo = $this->useDatabaseRuntime->connection(name: $connectionName);

        try {
            $stmt = $pdo->query(query: 'SELECT @@SESSION.tx_isolation as isolation');
            $row  = $stmt->fetch(mode: PDO::FETCH_ASSOC);

            if ($row) {
                return $this->sqlToIsolationLevel(sql: $row['isolation'] ?? '');
            }
        } catch (Throwable) {
        }

        return null;
    }

    private function sqlToIsolationLevel(string $sql) : int|null
    {
        $normalized = strtoupper(preg_replace('/\s+/', ' ', trim($sql)));

        return match ($normalized) {
            'READ UNCOMMITTED' => PDO::TRANSACTION_READ_UNCOMMITTED,
            'READ COMMITTED'   => PDO::TRANSACTION_READ_COMMITTED,
            'REPEATABLE READ'  => PDO::TRANSACTION_REPEATABLE_READ,
            'SERIALIZABLE'     => PDO::TRANSACTION_SERIALIZABLE,
            default            => null,
        };
    }

    private function isolationLevelToSql(int $level) : string
    {
        return match ($level) {
            PDO::TRANSACTION_READ_UNCOMMITTED => 'READ UNCOMMITTED',
            PDO::TRANSACTION_READ_COMMITTED   => 'READ COMMITTED',
            PDO::TRANSACTION_REPEATABLE_READ  => 'REPEATABLE READ',
            PDO::TRANSACTION_SERIALIZABLE     => 'SERIALIZABLE',
            default                           => 'REPEATABLE READ',
        };
    }

    public function inTransaction(string|null $connectionName = null) : bool
    {
        $pdo = $this->useDatabaseRuntime->connection(name: $connectionName);

        return $pdo->inTransaction();
    }

    public function commit(string|null $connectionName = null) : void
    {
        $pdo = $this->useDatabaseRuntime->connection(name: $connectionName);

        if ($pdo->inTransaction()) {
            $pdo->commit();
        }
    }

    public function rollback(string|null $connectionName = null) : void
    {
        $pdo = $this->useDatabaseRuntime->connection(name: $connectionName);

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
    }

    /**
     * @throws Exception
     */
    public function runWithRetry(
        callable    $callback,
        int|null    $maxRetries = null,
        int|null    $delayMs = null,
        string|null $connectionName = null
    ) : mixed
    {
        $maxRetries    ??= 3;
        $delayMs       ??= 100;
        $attempt       = 0;
        $lastException = null;

        while ( $attempt < $maxRetries ) {
            try {
                return $this->run(callback: $callback, connectionName: $connectionName);
            } catch (PersistentDataFailure $e) {
                $lastException = $e;

                if (! $this->isRetryable(failure: $e)) {
                    throw $e;
                }

                $attempt++;

                if ($attempt < $maxRetries) {
                    usleep($delayMs * 1000 * $attempt);
                }
            }
        }

        throw $lastException ?? new PersistentDataFailure(message: 'Transaction failed after retries.');
    }

    public function run(callable $callback, string|null $connectionName = null) : mixed
    {
        $pdo = $this->useDatabaseRuntime->connection(name: $connectionName);

        if (! $pdo->inTransaction()) {
            $pdo->beginTransaction();
        }

        try {
            $result = $callback($pdo);

            if ($pdo->inTransaction()) {
                $pdo->commit();
            }

            return $result;

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw new PersistentDataFailure(
                message : sprintf('Transaction failed: %s', $e->getMessage()),
                code    : $e->getCode(),
                previous: $e
            );
        }
    }

    private function isRetryable(PersistentDataFailure $failure) : bool
    {
        $code = $failure->getCode();

        $retryableCodes = [
            '40001',
            '1213',
            '1205',
            'HY000',
        ];

        return in_array($code, $retryableCodes, true);
    }

    public function begin(string|null $connectionName = null) : void
    {
        $pdo = $this->useDatabaseRuntime->connection(name: $connectionName);
        $pdo->beginTransaction();
    }

    public function setIsolation(int $isolationLevel, string|null $connectionName = null) : void
    {
        $pdo = $this->useDatabaseRuntime->connection(name: $connectionName);
        $pdo->exec(statement: sprintf('SET TRANSACTION ISOLATION LEVEL %s', $this->isolationLevelToSql(level: $isolationLevel)));
    }

    /**
     * @throws Throwable
     */
    public function savepoint(string $name, callable $callback, string|null $connectionName = null) : mixed
    {
        if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name)) {
            throw new InvalidArgumentException(message: 'Invalid savepoint name.');
        }

        $pdo = $this->useDatabaseRuntime->connection(name: $connectionName);

        $pdo->exec(statement: "SAVEPOINT {$name}");

        try {
            $result = $callback($pdo);

            return $result;
        } catch (Throwable $e) {
            $pdo->exec(statement: "ROLLBACK TO SAVEPOINT {$name}");
            throw $e;
        }
    }
}