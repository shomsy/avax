<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Transactions\RunTransaction;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\Contracts\DatabaseConnection;
use Avax\Components\DataStack\Database\System\Capabilities\Transactions\Contracts\TransactionsInterface;
use Avax\Components\DataStack\Database\System\Capabilities\Transactions\Exceptions\TransactionException;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\CompiledDatabaseLifecycleRegistry;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\AfterCommit;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\AfterRollback;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\TransactionBeginning;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\TransactionCommitted;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\TransactionRolledBack;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\TransactionLifecyclePhase;
use Throwable;

/**
 * Transaction manager for atomic database operations including nesting and savepoints.
 *
 * Integrated with the compiled database lifecycle registry:
 * - beginning: fires before BEGIN SQL
 * - committed: fires after COMMIT SQL (before afterCommit callbacks)
 * - afterCommit: fires after afterCommit callbacks run (outermost commit only)
 * - rolledBack: fires after ROLLBACK SQL (before afterRollback callbacks)
 * - afterRollback: fires after afterRollback callbacks run (outermost rollback only)
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

    private ?string $transactionId = null;

    /**
     * @param  DatabaseConnection  $databaseConnection  The physical persistence gateway to use.
     * @param  CompiledDatabaseLifecycleRegistry  $registry  Compiled lifecycle registry.
     * @param  string  $connectionName  Connection identifier for lifecycle events.
     */
    private function __construct(
        private readonly DatabaseConnection $databaseConnection,
        private readonly CompiledDatabaseLifecycleRegistry $registry = new CompiledDatabaseLifecycleRegistry(),
        private readonly string $connectionName = 'default',
    ) {
    }

    /**
     * Initialize a transaction manager on a specific connection.
     *
     * @param  DatabaseConnection  $databaseConnection  Physical gateway.
     * @param  CompiledDatabaseLifecycleRegistry|null  $registry  Optional compiled lifecycle registry.
     * @param  string  $connectionName  Connection identifier for lifecycle events.
     */
    public static function on(
        DatabaseConnection $databaseConnection,
        ?CompiledDatabaseLifecycleRegistry $registry = null,
        string $connectionName = 'default',
    ): self {
        return new self(
            databaseConnection: $databaseConnection,
            registry: $registry ?? new CompiledDatabaseLifecycleRegistry(),
            connectionName: $connectionName,
        );
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
        $start = microtime(as_float: true);

        try {
            if ($this->transactions === 0) {
                $this->transactionId = 'txn_'.bin2hex(random_bytes(4));
                $this->databaseConnection->getConnection()->beginTransaction();
            } else {
                // Create a bookmark for the inner bubble.
                $savepointName = 'sp_'.$this->transactions;
                $this->databaseConnection->getConnection()->exec(statement: 'SAVEPOINT '.$savepointName);
            }

            // Fire beginning lifecycle event on outermost only.
            if ($this->transactions === 0) {
                $this->dispatchTransactionLifecycle(
                    phase: TransactionLifecyclePhase::Beginning,
                    nestingLevel: 0,
                    durationMs: 0,
                    reason: null,
                );
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
        $start = microtime(as_float: true);

        try {
            if ($this->transactions === 0) {
                throw new TransactionException(
                    message     : 'Cannot commit: no active transaction',
                    nestingLevel: 0,
                );
            }

            if ($this->transactions === 1) {
                $this->databaseConnection->getConnection()->commit();

                // Outermost commit — run afterCommit callbacks.
                $callbacks = $this->afterCommitCallbacks;
                $this->afterCommitCallbacks = [];

                foreach ($callbacks as $callback) {
                    $callback();
                }
            } else {
                // Remove the inner bookmark.
                $savepointName = 'sp_'.($this->transactions - 1);
                $this->databaseConnection->getConnection()->exec(statement: 'RELEASE SAVEPOINT '.$savepointName);
            }

            $this->transactions = max(0, $this->transactions - 1);
            $durationMs = (microtime(as_float: true) - $start) * 1000;

            // Fire committed lifecycle event on outermost commit.
            if ($this->transactions === 0) {
                $this->dispatchTransactionLifecycle(
                    phase: TransactionLifecyclePhase::Committed,
                    nestingLevel: 0,
                    durationMs: $durationMs,
                    reason: null,
                );

                // Fire afterCommit lifecycle event after callbacks have run.
                $this->dispatchTransactionLifecycle(
                    phase: TransactionLifecyclePhase::AfterCommit,
                    nestingLevel: 0,
                    durationMs: $durationMs,
                    reason: null,
                );

                $this->transactionId = null;
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

                // Fire rolledBack lifecycle event.
                $this->dispatchTransactionLifecycle(
                    phase: TransactionLifecyclePhase::RolledBack,
                    nestingLevel: 0,
                    durationMs: 0,
                    reason: null,
                );

                // Fire afterRollback lifecycle event after callbacks have run.
                $this->dispatchTransactionLifecycle(
                    phase: TransactionLifecyclePhase::AfterRollback,
                    nestingLevel: 0,
                    durationMs: 0,
                    reason: null,
                );

                $this->transactions = 0;
                $this->transactionId = null;
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

            // Fire rolledBack lifecycle event even on rollback failure.
            $this->dispatchTransactionLifecycle(
                phase: TransactionLifecyclePhase::RolledBack,
                nestingLevel: 0,
                durationMs: 0,
                reason: $throwable,
            );

            $this->dispatchTransactionLifecycle(
                phase: TransactionLifecyclePhase::AfterRollback,
                nestingLevel: 0,
                durationMs: 0,
                reason: $throwable,
            );

            $this->transactionId = null;

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

    /**
     * Dispatch transaction lifecycle events through the compiled registry.
     *
     * No-listener path: registry returns empty list, zero overhead.
     * Listener failure bubbles by default.
     */
    private function dispatchTransactionLifecycle(
        TransactionLifecyclePhase $phase,
        int $nestingLevel,
        float $durationMs,
        ?Throwable $reason,
    ): void {
        $listeners = $this->registry->transactionListenersFor($phase);
        if ($listeners === []) {
            return;
        }

        $event = match ($phase) {
            TransactionLifecyclePhase::Beginning => new TransactionBeginning(
                connection: $this->connectionName,
                nestingLevel: $nestingLevel,
                transactionId: $this->transactionId ?? '',
            ),
            TransactionLifecyclePhase::Committed => new TransactionCommitted(
                connection: $this->connectionName,
                nestingLevel: $nestingLevel,
                transactionId: $this->transactionId ?? '',
                durationMs: $durationMs,
            ),
            TransactionLifecyclePhase::AfterCommit => new AfterCommit(
                connection: $this->connectionName,
                transactionId: $this->transactionId ?? '',
            ),
            TransactionLifecyclePhase::RolledBack => new TransactionRolledBack(
                connection: $this->connectionName,
                nestingLevel: $nestingLevel,
                transactionId: $this->transactionId ?? '',
            ),
            TransactionLifecyclePhase::AfterRollback => new AfterRollback(
                connection: $this->connectionName,
                transactionId: $this->transactionId ?? '',
                reason: $reason,
            ),
            TransactionLifecyclePhase::Failed => throw new \RuntimeException('Failed phase should not be dispatched directly'),
        };

        foreach ($listeners as $entry) {
            $listener = $entry['listener'];
            $instance = new $listener();
            // @phpstan-ignore-next-line
            $instance($event);
        }
    }
}
