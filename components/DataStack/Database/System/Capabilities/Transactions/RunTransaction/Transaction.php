<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Transactions\RunTransaction;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\Contracts\DatabaseConnection;
use Avax\Components\DataStack\Database\System\Capabilities\Transactions\Contracts\TransactionsInterface;
use Avax\Components\DataStack\Database\System\Capabilities\Transactions\Exceptions\TransactionException;
use Throwable;

/**
 * Transaction manager for atomic database operations including nesting and savepoints.
 *
 * @see /docs/Foundation/Database/DSL/Transactions.md
 */
final class Transaction implements TransactionsInterface
{
    /** @var int How many bubbles deep are we currently? (0 = no transaction active). */
    private int $transactions = 0;

    /** @var list<callable> afterCommit callbacks buffered for the outermost transaction. */
    private array $afterCommitCallbacks = [];

    /** @var list<callable> afterRollback callbacks buffered for the outermost transaction. */
    private array $afterRollbackCallbacks = [];

    /**
     * @param  DatabaseConnection  $databaseConnection  The physical persistence gateway to use.
     */
    private function __construct(private readonly DatabaseConnection $databaseConnection)
    {
    }

    /**
     * Initialize a transaction manager on a specific connection.
     *
     * @param  DatabaseConnection  $databaseConnection  Physical gateway.
     */
    public static function on(DatabaseConnection $databaseConnection): self
    {
        return new self(databaseConnection: $databaseConnection);
    }

    /**
     * A cleaner name for starting a transaction.
     *
     * @param  callable  $callback  The code you want to protect.
     * @return mixed Whatever your code returns.
     */
    public function run(callable $callback): mixed
    {
        return $this->transaction(callback: $callback);
    }

    /**
     * Execute a closure within a transaction bubble.
     *
     * @param  callable  $callback  Logic to protect.
     */
    public function transaction(callable $callback): mixed
    {
        $this->begin();

        try {
            $result = $callback($this);
            $this->commit();

            return $result;
        } catch (Throwable $throwable) {
            try {
                $this->rollback();
            } catch (Throwable) {
                // We ignore rollback errors to make sure we show you the REAL error that happened first.
            }

            if ($throwable instanceof TransactionException) {
                throw $throwable;
            }

            throw new TransactionException(
                message     : 'Transaction failed: '.$throwable->getMessage(),
                nestingLevel: $this->transactions,
                previous    : $throwable,
            );
        }
    }

    /**
     * Begin a new transaction or create a savepoint if already active.
     */
    public function begin(): self
    {
        try {
            if ($this->transactions === 0) {
                $this->databaseConnection->getConnection()->beginTransaction();
            } else {
                // Create a bookmark for the inner bubble.
                $savepointName = 'sp_'.$this->transactions;
                $this->databaseConnection->getConnection()->exec(statement: 'SAVEPOINT '.$savepointName);
            }

            $this->transactions++;
        } catch (Throwable $throwable) {
            throw new TransactionException(
                message     : 'Failed to begin transaction: '.$throwable->getMessage(),
                nestingLevel: $this->transactions,
                previous    : $throwable,
            );
        }

        return $this;
    }

    /**
     * Register a callback to run after successful outermost commit.
     *
     * If no transaction is active, the callback runs immediately (no-transaction policy).
     * Callbacks are discarded on rollback — they only run on successful commit.
     */
    public function afterCommit(callable $callback): void
    {
        if ($this->transactions === 0) {
            // No active transaction — run immediately per design lock no-transaction policy.
            $callback();

            return;
        }

        $this->afterCommitCallbacks[] = $callback;
    }

    /**
     * Register a callback to run after rollback.
     *
     * Callbacks only run on actual rollback, not on commit failure.
     */
    public function afterRollback(callable $callback): void
    {
        $this->afterRollbackCallbacks[] = $callback;
    }

    /**
     * Get the current transaction nesting level.
     */
    public function getNestingLevel(): int
    {
        return $this->transactions;
    }

    /**
     * Access the connection being used for this transaction.
     */
    public function getConnection(): DatabaseConnection
    {
        return $this->databaseConnection;
    }

    /**
     * Commit the current transaction or release the most recent savepoint.
     */
    public function commit(): self
    {
        try {
            if ($this->transactions === 0) {
                throw new TransactionException(
                    message     : 'Cannot commit: no active transaction',
                    nestingLevel: 0,
                );
            }

            if ($this->transactions === 1) {
                $this->databaseConnection->getConnection()->commit();
            } else {
                // Remove the inner bookmark.
                $savepointName = 'sp_'.($this->transactions - 1);
                $this->databaseConnection->getConnection()->exec(statement: 'RELEASE SAVEPOINT '.$savepointName);
            }

            $this->transactions = max(0, $this->transactions - 1);

            // Outermost commit — run afterCommit callbacks.
            if ($this->transactions === 0) {
                $callbacks = $this->afterCommitCallbacks;
                $this->afterCommitCallbacks = [];

                foreach ($callbacks as $callback) {
                    $callback();
                }
            }
        } catch (Throwable $throwable) {
            throw new TransactionException(
                message     : 'Failed to commit transaction: '.$throwable->getMessage(),
                nestingLevel: $this->transactions,
                previous    : $throwable,
            );
        }

        return $this;
    }

    /**
     * Rollback the current transaction or revert to the most recent savepoint.
     */
    public function rollback(): self
    {
        try {
            if ($this->transactions === 0) {
                throw new TransactionException(
                    message     : 'Cannot rollback: no active transaction',
                    nestingLevel: 0,
                );
            }

            if ($this->transactions === 1) {
                $this->databaseConnection->getConnection()->rollBack();

                // Outermost rollback — clear afterCommit callbacks, run afterRollback.
                $afterRollbackCallbacks = $this->afterRollbackCallbacks;
                $this->afterCommitCallbacks = [];
                $this->afterRollbackCallbacks = [];

                foreach ($afterRollbackCallbacks as $callback) {
                    $callback();
                }

                $this->transactions = 0;
            } else {
                // Revert back to the inner bookmark.
                $savepointName = 'sp_'.($this->transactions - 1);
                $this->databaseConnection->getConnection()->exec(statement: 'ROLLBACK TO SAVEPOINT '.$savepointName);
                $this->transactions = max(0, $this->transactions - 1);
            }
        } catch (Throwable $throwable) {
            $this->transactions = 0;

            // Clear callbacks on rollback failure too.
            $this->afterCommitCallbacks = [];
            $this->afterRollbackCallbacks = [];

            throw new TransactionException(
                message     : 'Failed to rollback transaction: '.$throwable->getMessage(),
                nestingLevel: $this->transactions,
                previous    : $throwable,
            );
        }

        return $this;
    }

    /**
     * Create an automatic, RAII-style transaction scope.
     *
     * @param  callable  $callback  Logic to run within the scope.
     *
     * @throws Throwable
     */
    public function scope(callable $callback): mixed
    {
        $transactionScope = new TransactionScope(manager: $this);

        $result = $callback($transactionScope);
        $transactionScope->complete();

        return $result;
    }

    /**
     * Create a named savepoint (bookmark) within the active transaction.
     *
     * @param  string  $name  Unique savepoint identifier.
     */
    public function savepoint(string $name): self
    {
        if (! $this->isValidSavepointName(name: $name)) {
            throw new TransactionException(
                message     : sprintf('Invalid savepoint name: %s. Only alphanumeric characters and underscores are allowed.', $name),
                nestingLevel: $this->transactions,
            );
        }

        try {
            $this->databaseConnection->getConnection()->exec(statement: 'SAVEPOINT '.$name);
        } catch (Throwable $throwable) {
            throw new TransactionException(
                message     : sprintf('Failed to create savepoint [%s]: ', $name).$throwable->getMessage(),
                nestingLevel: $this->transactions,
                previous    : $throwable,
            );
        }

        return $this;
    }

    /**
     * Check if a bookmark nickname is safe to use.
     */
    private function isValidSavepointName(string $name): bool
    {
        return $name !== ''
            && strlen(string: $name) <= 64
            && preg_match(pattern: '/^\w+$/', subject: $name) === 1;
    }

    /**
     * Undo everything back to a specific "Bookmark" (Savepoint).
     */
    public function rollbackTo(string $name): self
    {
        if (! $this->isValidSavepointName(name: $name)) {
            throw new TransactionException(
                message     : sprintf('Invalid savepoint name: %s. Only alphanumeric characters and underscores are allowed.', $name),
                nestingLevel: $this->transactions,
            );
        }

        try {
            $this->databaseConnection->getConnection()->exec(statement: 'ROLLBACK TO SAVEPOINT '.$name);
        } catch (Throwable $throwable) {
            throw new TransactionException(
                message     : sprintf('Failed to rollback to savepoint [%s]: ', $name).$throwable->getMessage(),
                nestingLevel: $this->transactions,
                previous    : $throwable,
            );
        }

        return $this;
    }
}
