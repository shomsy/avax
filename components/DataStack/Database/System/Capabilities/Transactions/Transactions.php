<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Transactions;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\Contracts\DatabaseConnection;
use Closure;
use RuntimeException;
use Throwable;

/**
 * Manages database transactions with isolation level support,
 * begin/commit/rollback, and nested transaction support via savepoints.
 */
final class Transactions
{
    /**
     * @var int Current nesting depth (0 = no active transaction)
     */
    private int $depth = 0;

    /**
     * @var list<string> Stack of savepoint names for nested transactions
     */
    private array $savepoints = [];

    /**
     * @var bool Whether a transaction is currently active
     */
    private bool $active = false;

    /**
     * @var list<Closure() : void> Callbacks to execute on successful commit
     */
    private array $commitCallbacks = [];

    /**
     * @var list<Closure() : void> Callbacks to execute on rollback
     */
    private array $rollbackCallbacks = [];

    public function __construct(private readonly DatabaseConnection $databaseConnection)
    {
    }

    /**
     * Executes a closure within a transaction with retry logic for deadlocks.
     *
     * @template T
     *
     * @param  Closure(self) : T  $callback
     * @return T
     *
     * @throws Throwable
     */
    public function transactionWithRetry(
        Closure $callback,
        ?IsolationLevel $isolationLevel = null,
        ?RetryPolicy $retryPolicy = null,
    ): mixed {
        $policy = $retryPolicy ?? RetryPolicy::forDeadlocks();
        $attempt = 0;
        $lastException = null;

        while ($attempt < $policy->maxAttempts) {
            try {
                return $this->transaction($callback, $isolationLevel);
            } catch (Throwable $e) {
                $lastException = $e;
                $attempt++;

                if (! $policy->shouldRetry($e, $attempt)) {
                    throw $e;
                }

                $delayUs = $policy->getDelayMs($attempt - 1) * 1000;
                usleep($delayUs);
            }
        }

        throw $lastException;
    }

    /**
     * Executes a closure within a transaction context.
     *
     * Automatically handles begin/commit/rollback and supports nesting.
     *
     * @template T
     *
     * @param  Closure(self) : T  $callback
     * @return T
     *
     * @throws Throwable Re-throws the original exception after rollback
     */
    public function transaction(Closure $callback, ?IsolationLevel $isolationLevel = null): mixed
    {
        $this->begin($isolationLevel);

        try {
            $result = $callback($this);
            $this->commit();

            return $result;
        } catch (Throwable $throwable) {
            try {
                $this->rollback();
            } catch (Throwable $rollbackError) {
                // If rollback fails, we still throw the original exception
                // but log the rollback failure
                error_log('Rollback failed: '.$rollbackError->getMessage());
            }

            throw $throwable;
        }
    }

    /**
     * Begins a new transaction with the specified isolation level.
     *
     * @throws RuntimeException If a transaction is already active at the root level
     */
    public function begin(?IsolationLevel $isolationLevel = null): void
    {
        if ($this->depth === 0) {
            $this->databaseConnection->beginTransaction();
            $this->active = true;

            if ($isolationLevel instanceof IsolationLevel) {
                $this->databaseConnection->exec($isolationLevel->toSql());
            }
        } else {
            $savepointName = $this->generateSavepointName();
            $this->databaseConnection->exec('SAVEPOINT '.$savepointName);
            $this->savepoints[] = $savepointName;
        }

        $this->depth++;
    }

    /**
     * Generates a unique savepoint name.
     */
    private function generateSavepointName(): string
    {
        return 'avax_sp_'.$this->depth.'_'.spl_object_id($this).'_'.hrtime(true);
    }

    /**
     * Commits the current transaction or releases the current savepoint.
     *
     * @throws RuntimeException If no transaction is active
     */
    public function commit(): void
    {
        if ($this->depth === 0) {
            throw new RuntimeException('Cannot commit: no transaction is active');
        }

        if ($this->depth === 1) {
            $this->databaseConnection->commit();
            $this->active = false;
            $this->executeCommitCallbacks();
        } else {
            $savepointName = array_pop($this->savepoints);
            $this->databaseConnection->exec('RELEASE SAVEPOINT '.$savepointName);
        }

        $this->depth--;
    }

    /**
     * Executes all registered commit callbacks and clears the list.
     */
    private function executeCommitCallbacks(): void
    {
        foreach ($this->commitCallbacks as $commitCallback) {
            try {
                $commitCallback();
            } catch (Throwable $e) {
                error_log('After-commit callback failed: '.$e->getMessage());
            }
        }

        $this->commitCallbacks = [];
    }

    /**
     * Rolls back the current transaction or the current savepoint.
     *
     * @throws RuntimeException If no transaction is active
     */
    public function rollback(): void
    {
        if ($this->depth === 0) {
            throw new RuntimeException('Cannot rollback: no transaction is active');
        }

        if ($this->depth === 1) {
            $this->databaseConnection->rollBack();
            $this->active = false;
            $this->executeRollbackCallbacks();
        } else {
            $savepointName = array_pop($this->savepoints);
            $this->databaseConnection->exec('ROLLBACK TO SAVEPOINT '.$savepointName);
        }

        $this->depth--;
    }

    /**
     * Executes all registered rollback callbacks and clears the list.
     */
    private function executeRollbackCallbacks(): void
    {
        foreach ($this->rollbackCallbacks as $rollbackCallback) {
            try {
                $rollbackCallback();
            } catch (Throwable $e) {
                error_log('After-rollback callback failed: '.$e->getMessage());
            }
        }

        $this->rollbackCallbacks = [];
    }

    /**
     * Registers a callback to be executed after a successful commit.
     */
    public function afterCommit(Closure $callback): void
    {
        $this->commitCallbacks[] = $callback;
    }

    /**
     * Registers a callback to be executed after a rollback.
     */
    public function afterRollback(Closure $callback): void
    {
        $this->rollbackCallbacks[] = $callback;
    }

    /**
     * Returns the current transaction nesting depth.
     * 0 means no transaction is active.
     */
    public function depth(): int
    {
        return $this->depth;
    }

    /**
     * Returns whether a transaction is currently active.
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * Returns the current savepoint name (for the innermost nested transaction).
     */
    public function currentSavepoint(): ?string
    {
        return $this->savepoints === [] ? null : end($this->savepoints);
    }

    /**
     * Forcefully resets the transaction state.
     *
     * Use with caution - this does not interact with the database.
     */
    public function reset(): void
    {
        $this->depth = 0;
        $this->active = false;
        $this->savepoints = [];
        $this->commitCallbacks = [];
        $this->rollbackCallbacks = [];
    }
}
