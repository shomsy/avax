<?php

declare(strict_types=1);

namespace Avax\DataLayer\CommitDataChanges;

use Avax\DataLayer\AccessPersistentData\UseDatabaseRuntime;
use Avax\DataLayer\ConfigureDataLayer\DataLayerRuntime;
use Exception;
use InvalidArgumentException;
use PDO;
use Random\RandomException;
use Throwable;

final readonly class CommitDataChanges
{
    public function __construct(
        private UseDatabaseRuntime     $useDatabaseRuntime,
        private DataTransactionPolicy|null $policy = null
    )
    {
        $this->policy ??= DataTransactionPolicy::strict();
    }

    public static function withRuntime(DataLayerRuntime $runtime, DataTransactionPolicy|null $policy = null) : self
    {
        return new self(
            useDatabaseRuntime: new UseDatabaseRuntime(runtime: $runtime),
            policy            : $policy
        );
    }

    public function begin(string|null $connectionName = null) : DataTransaction
    {
        $id  = $this->generateTransactionId();
        $pdo = $this->useDatabaseRuntime->connection(name: $connectionName);

        if ($this->policy->defaultIsolation !== IsolationLevel::READ_COMMITTED) {
            $pdo->exec(statement: sprintf(
                           'SET TRANSACTION ISOLATION LEVEL %s',
                           $this->policy->defaultIsolation->value
                       ));
        }

        $pdo->beginTransaction();

        return DataTransaction::started(
            id            : $id,
            connectionName: $connectionName,
            isolationLevel: $this->policy->defaultIsolation
        );
    }

    public function commit(DataTransaction $transaction) : DataTransaction
    {
        $pdo = $this->useDatabaseRuntime->connection(name: $transaction->connectionName);

        if (! $pdo->inTransaction()) {
            throw new DataTransactionFailure(
                message: 'Cannot commit: no active transaction.',
                code   : '25000'
            );
        }

        $pdo->commit();

        return DataTransaction::committed(
            id            : $transaction->id,
            connectionName: $transaction->connectionName,
            isolationLevel: $transaction->isolationLevel,
            affectedRows  : $transaction->affectedRows,
            startedAt     : $transaction->startedAt
        );
    }

    public function rollback(DataTransaction $transaction) : DataTransaction
    {
        $pdo = $this->useDatabaseRuntime->connection(name: $transaction->connectionName);

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return DataTransaction::rolledBack(
            id            : $transaction->id,
            connectionName: $transaction->connectionName,
            isolationLevel: $transaction->isolationLevel,
            affectedRows  : $transaction->affectedRows,
            startedAt     : $transaction->startedAt
        );
    }

    public function execute(callable $operations, string|null $connectionName = null) : DataTransaction
    {
        $pdo = $this->useDatabaseRuntime->connection(name: $connectionName);
        $startedAt    = microtime(true);
        $id           = $this->generateTransactionId();
        $affectedRows = [];

        $transaction = DataTransaction::started(
            id            : $id,
            connectionName: $connectionName,
            isolationLevel: $this->policy->defaultIsolation
        );

        try {
            $result = $operations($pdo, $transaction);

            if ($pdo->inTransaction()) {
                $pdo->commit();
            }

            $transaction = DataTransaction::committed(
                id            : $id,
                connectionName: $connectionName,
                isolationLevel: $this->policy->defaultIsolation,
                affectedRows  : $affectedRows,
                startedAt     : $startedAt
            );

            return $transaction;

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw new DataTransactionFailure(
                message : sprintf('Transaction failed: %s', $e->getMessage()),
                code    : $e->getCode() ?: 'HY000',
                previous: $e
            );
        }
    }

    /**
     * @throws Exception
     */
    public function executeWithRetry(
        callable    $operations,
        string|null $connectionName = null,
        int|null    $maxRetries = null
    ) : DataTransaction
    {
        $maxRetries    ??= $this->policy->maxRetries ?? 3;
        $attempt       = 0;
        $lastException = null;

        while ( $attempt < $maxRetries ) {
            try {
                return $this->execute(operations: $operations, connectionName: $connectionName);
            } catch (DataTransactionFailure $e) {
                $lastException = $e;

                if (! $this->isRetryable(failure: $e)) {
                    throw $e;
                }

                $attempt++;

                if ($attempt < $maxRetries) {
                    $delay = ($this->policy->retryDelayMs ?? 100) * $attempt;
                    usleep($delay * 1000);
                }
            }
        }

        throw $lastException ?? new DataTransactionFailure(
            message: 'Transaction failed after maximum retries.'
        );
    }

    public function createSavepoint(string $name) : string
    {
        if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name)) {
            throw new InvalidArgumentException(message: 'Invalid savepoint name.');
        }

        $pdo = $this->useDatabaseRuntime->connection();
        $pdo->exec(statement: "SAVEPOINT {$name}");

        return $name;
    }

    public function releaseSavepoint(string $name) : void
    {
        $pdo = $this->useDatabaseRuntime->connection();
        $pdo->exec(statement: "RELEASE SAVEPOINT {$name}");
    }

    public function rollbackToSavepoint(string $name) : void
    {
        $pdo = $this->useDatabaseRuntime->connection();
        $pdo->exec(statement: "ROLLBACK TO SAVEPOINT {$name}");
    }

    public function getIsolationLevel() : IsolationLevel
    {
        return $this->policy->defaultIsolation;
    }

    public function setIsolationLevel(IsolationLevel $level, string|null $connectionName = null) : void
    {
        $pdo = $this->useDatabaseRuntime->connection(name: $connectionName);
        $pdo->exec(statement: sprintf('SET TRANSACTION ISOLATION LEVEL %s', $level->value));
    }

    public function inTransaction(string|null $connectionName = null) : bool
    {
        $pdo = $this->useDatabaseRuntime->connection(name: $connectionName);

        return $pdo->inTransaction();
    }

    public function isLocked(string|null $connectionName = null) : bool
    {
        $pdo = $this->useDatabaseRuntime->connection(name: $connectionName);

        try {
            $pdo->exec(statement: 'SELECT 1 FOR UPDATE');

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public function getTransactionCount(string|null $connectionName = null) : int
    {
        $pdo = $this->useDatabaseRuntime->connection(name: $connectionName);

        try {
            $stmt   = $pdo->query(query: 'SELECT @@GLOBAL.trx_count');
            $result = $stmt->fetch(mode: PDO::FETCH_ASSOC);

            return (int) ($result['trx_count'] ?? 0);
        } catch (Throwable) {
            return 0;
        }
    }

    /**
     * @throws RandomException
     */
    private function generateTransactionId() : string
    {
        return sprintf(
            'txn_%s_%d_%s',
            date('YmdHis'),
            getmypid(),
            bin2hex(random_bytes(4))
        );
    }

    private function isRetryable(DataTransactionFailure $failure) : bool
    {
        if (! $this->policy->allowDeadlockRetries) {
            return false;
        }

        $code = $failure->getCode();

        $retryableCodes = ['40001', '1213', '1205', 'HY000'];

        return in_array($code, $retryableCodes, true);
    }
}