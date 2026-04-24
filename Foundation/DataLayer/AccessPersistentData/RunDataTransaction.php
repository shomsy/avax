<?php

declare(strict_types=1);

namespace Avax\DataLayer\AccessPersistentData;

use InvalidArgumentException;
use PDO;
use Throwable;

final readonly class RunDataTransaction
{
    private const DEFAULT_ISOLATION = PDO::TRANSACTION_REPEATABLE_READ;
    private const DEFAULT_TIMEOUT   = 60;

    public function __construct(private UseDatabaseRuntime $useDatabaseRuntime) {}

    public function run(callable $callback, ?string $connectionName = null) : mixed
    {
        $pdo = $this->useDatabaseRuntime->connection($connectionName);

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
                          sprintf('Transaction failed: %s', $e->getMessage()),
                code    : $e->getCode(),
                previous: $e
            );
        }
    }

    public function runWithIsolation(
        callable $callback,
        int      $isolationLevel = self::DEFAULT_ISOLATION,
        ?string  $connectionName = null
    ) : mixed
    {
        $pdo               = $this->useDatabaseRuntime->connection($connectionName);
        $previousIsolation = $this->getCurrentIsolation($pdo);

        try {
            $pdo->exec(sprintf('SET TRANSACTION ISOLATION LEVEL %s', $this->isolationLevelToSql($isolationLevel)));
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
                    $pdo->exec(sprintf('SET TRANSACTION ISOLATION LEVEL %s', $this->isolationLevelToSql($previousIsolation)));
                } catch (Throwable) {
                }
            }

            throw new PersistentDataFailure(
                          sprintf('Transaction with isolation %s failed: %s', $this->isolationLevelToSql($isolationLevel), $e->getMessage()),
                code    : $e->getCode(),
                previous: $e
            );
        }
    }

    public function runWithRetry(
        callable $callback,
        int      $maxRetries = 3,
        int      $delayMs = 100,
        ?string  $connectionName = null
    ) : mixed
    {
        $attempt       = 0;
        $lastException = null;

        while ( $attempt < $maxRetries ) {
            try {
                return $this->run($callback, $connectionName);
            } catch (PersistentDataFailure $e) {
                $lastException = $e;

                if (! $this->isRetryable($e)) {
                    throw $e;
                }

                $attempt++;

                if ($attempt < $maxRetries) {
                    usleep($delayMs * 1000 * $attempt);
                }
            }
        }

        throw $lastException ?? new PersistentDataFailure('Transaction failed after retries.');
    }

    public function begin(?string $connectionName = null) : void
    {
        $pdo = $this->useDatabaseRuntime->connection($connectionName);
        $pdo->beginTransaction();
    }

    public function commit(?string $connectionName = null) : void
    {
        $pdo = $this->useDatabaseRuntime->connection($connectionName);

        if ($pdo->inTransaction()) {
            $pdo->commit();
        }
    }

    public function rollback(?string $connectionName = null) : void
    {
        $pdo = $this->useDatabaseRuntime->connection($connectionName);

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
    }

    public function inTransaction(?string $connectionName = null) : bool
    {
        $pdo = $this->useDatabaseRuntime->connection($connectionName);

        return $pdo->inTransaction();
    }

    public function setIsolation(int $isolationLevel, ?string $connectionName = null) : void
    {
        $pdo = $this->useDatabaseRuntime->connection($connectionName);
        $pdo->exec(sprintf('SET TRANSACTION ISOLATION LEVEL %s', $this->isolationLevelToSql($isolationLevel)));
    }

    public function getCurrentIsolation(?string $connectionName = null) : ?int
    {
        $pdo = $this->useDatabaseRuntime->connection($connectionName);

        try {
            $stmt = $pdo->query('SELECT @@SESSION.tx_isolation as isolation');
            $row  = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                return $this->sqlToIsolationLevel($row['isolation'] ?? '');
            }
        } catch (Throwable) {
        }

        return null;
    }

    public function savepoint(string $name, callable $callback, ?string $connectionName = null) : mixed
    {
        if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name)) {
            throw new InvalidArgumentException('Invalid savepoint name.');
        }

        $pdo = $this->useDatabaseRuntime->connection($connectionName);

        $pdo->exec("SAVEPOINT {$name}");

        try {
            $result = $callback($pdo);

            return $result;
        } catch (Throwable $e) {
            $pdo->exec("ROLLBACK TO SAVEPOINT {$name}");
            throw $e;
        }
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

    private function sqlToIsolationLevel(string $sql) : ?int
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
}