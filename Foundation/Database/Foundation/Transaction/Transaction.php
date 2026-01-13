<?php

declare(strict_types=1);

namespace Avax\Database\Transaction;

use Avax\Database\Connection\Contracts\DatabaseConnection;
use Avax\Database\Transaction\Contracts\TransactionManagerInterface;
use Avax\Database\Transaction\Exceptions\TransactionException;
use Throwable;

/**
 * Transaction manager for atomic database operations including nesting and savepoints.
 *
 * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/Transactions.md
 */
final class Transaction implements TransactionManagerInterface
{
    /** @var int How many bubbles deep are we currently? (0 = no transaction active). */
    private int $transactions = 0;

    /**
     * @param  DatabaseConnection  $connection  The physical persistence gateway to use.
     */
    private function __construct(
        private readonly DatabaseConnection $connection
    ) {}

    /**
     * Initialize a transaction manager on a specific connection.
     *
     * @param  DatabaseConnection  $connection  Physical gateway.
     */
    public static function on(DatabaseConnection $connection): self
    {
        return new self(connection: $connection);
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
        } catch (Throwable $e) {
            try {
                $this->rollback();
            } catch (Throwable) {
                // We ignore rollback errors to make sure we show you the REAL error that happened first.
            }

            if ($e instanceof TransactionException) {
                throw $e;
            }

            throw new TransactionException(
                message     : 'Transaction failed: '.$e->getMessage(),
                nestingLevel: $this->transactions,
                previous    : $e
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
                $this->connection->getConnection()->beginTransaction();
            } else {
                // Create a bookmark for the inner bubble.
                $savepointName = 'sp_'.$this->transactions;
                $this->connection->getConnection()->exec(statement: "SAVEPOINT {$savepointName}");
            }

            $this->transactions++;
        } catch (Throwable $e) {
            throw new TransactionException(
                message     : 'Failed to begin transaction: '.$e->getMessage(),
                nestingLevel: $this->transactions,
                previous    : $e
            );
        }

        return $this;
    }

    /**
     * Access the connection being used for this transaction.
     */
    public function getConnection(): DatabaseConnection
    {
        return $this->connection;
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
                    previous    : null
                );
            }

            if ($this->transactions === 1) {
                $this->connection->getConnection()->commit();
            } else {
                // Remove the inner bookmark.
                $savepointName = 'sp_'.($this->transactions - 1);
                $this->connection->getConnection()->exec(statement: "RELEASE SAVEPOINT {$savepointName}");
            }

            $this->transactions = max(0, $this->transactions - 1);
        } catch (Throwable $e) {
            throw new TransactionException(
                message     : 'Failed to commit transaction: '.$e->getMessage(),
                nestingLevel: $this->transactions,
                previous    : $e
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
                    previous    : null
                );
            }

            if ($this->transactions === 1) {
                $this->connection->getConnection()->rollBack();
                $this->transactions = 0;
            } else {
                // Revert back to the inner bookmark.
                $savepointName = 'sp_'.($this->transactions - 1);
                $this->connection->getConnection()->exec(statement: "ROLLBACK TO SAVEPOINT {$savepointName}");
                $this->transactions = max(0, $this->transactions - 1);
            }
        } catch (Throwable $e) {
            $this->transactions = 0;
            throw new TransactionException(
                message     : 'Failed to rollback transaction: '.$e->getMessage(),
                nestingLevel: $this->transactions,
                previous    : $e
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
        $scope = new TransactionScope(manager: $this);

        try {
            $result = $callback($scope);
            $scope->complete();

            return $result;
        } catch (Throwable $e) {
            // The scope object's destructor will handle the rollback for us.
            throw $e;
        }
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
                message     : "Invalid savepoint name: {$name}. Only alphanumeric characters and underscores are allowed.",
                nestingLevel: $this->transactions,
                previous    : null
            );
        }

        try {
            $this->connection->getConnection()->exec(statement: "SAVEPOINT {$name}");
        } catch (Throwable $e) {
            throw new TransactionException(
                message     : "Failed to create savepoint [{$name}]: ".$e->getMessage(),
                nestingLevel: $this->transactions,
                previous    : $e
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
            && preg_match(pattern: '/^[a-zA-Z0-9_]+$/', subject: $name) === 1;
    }

    /**
     * Undo everything back to a specific "Bookmark" (Savepoint).
     */
    public function rollbackTo(string $name): self
    {
        if (! $this->isValidSavepointName(name: $name)) {
            throw new TransactionException(
                message     : "Invalid savepoint name: {$name}. Only alphanumeric characters and underscores are allowed.",
                nestingLevel: $this->transactions,
                previous    : null
            );
        }

        try {
            $this->connection->getConnection()->exec(statement: "ROLLBACK TO SAVEPOINT {$name}");
        } catch (Throwable $e) {
            throw new TransactionException(
                message     : "Failed to rollback to savepoint [{$name}]: ".$e->getMessage(),
                nestingLevel: $this->transactions,
                previous    : $e
            );
        }

        return $this;
    }
}
