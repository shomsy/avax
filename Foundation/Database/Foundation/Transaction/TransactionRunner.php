<?php

declare(strict_types=1);

namespace Avax\Database\Transaction;

use Avax\Database\Connection\Contracts\DatabaseConnection;
use Avax\Database\Transaction\Contracts\TransactionManagerInterface;
use Avax\Database\Transaction\Exceptions\TransactionException;
use Throwable;

/**
 * Standard implementation of the transaction manager contract.
 */
class TransactionRunner implements TransactionManagerInterface
{
    /**
     * Tracks the current transaction nesting level.
     */
    private int $transactions = 0;

    /**
     * Initialize transaction runner with database connection.
     *
     * @param DatabaseConnection $connection Authority for database connections
     */
    public function __construct(
        private readonly DatabaseConnection $connection
    ) {}

    /**
     * Orchestrate the execution of a closure within a secured transaction block.
     *
     * -- intent: provide a robust wrapper for safe data mutations with automatic rollback.
     *
     * @param callable $callback Operational logic
     *
     * @return mixed
     * @throws TransactionException If the transaction fails and cannot be recovered
     */
    public function transaction(callable $callback) : mixed
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
                // Prevent rollback error from masking the primary exception
            }

            if ($e instanceof TransactionException) {
                throw $e;
            }

            throw new TransactionException(
                message     : "Callback execution failed: " . $e->getMessage(),
                nestingLevel: $this->transactions,
                previous    : $e
            );
        }
    }

    /**
     * Physically signal the start of a transaction to the database engine.
     *
     * -- intent: increment nesting state and dispatch BEGIN command to the driver.
     *
     * @return void
     * @throws TransactionException If driver fails to begin transaction
     */
    public function begin() : void
    {
        try {
            if ($this->transactions === 0) {
                $this->connection->getConnection()->beginTransaction();
            }

            $this->transactions++;
        } catch (Throwable $e) {
            throw new TransactionException(
                message     : "Failed to begin transaction: " . $e->getMessage(),
                nestingLevel: $this->transactions,
                previous    : $e
            );
        }
    }

    /**
     * Dispatch a COMMIT signal if the nesting level is at the absolute root.
     *
     * -- intent: finalize the transaction and update the nesting level.
     *
     * @return void
     * @throws TransactionException If driver fails to commit
     */
    public function commit() : void
    {
        try {
            if ($this->transactions === 1) {
                $this->connection->getConnection()->commit();
            }

            $this->transactions = max(0, $this->transactions - 1);
        } catch (Throwable $e) {
            throw new TransactionException(
                message     : "Failed to commit transaction: " . $e->getMessage(),
                nestingLevel: $this->transactions,
                previous    : $e
            );
        }
    }

    /**
     * Dispatch a ROLLBACK signal and reset the internal transaction state.
     *
     * -- intent: fulfill safe recovery by instructing the driver to discard active changes.
     *
     * @return void
     * @throws TransactionException If driver fails to rollback
     */
    public function rollback() : void
    {
        try {
            if ($this->transactions === 1) {
                $this->connection->getConnection()->rollBack();
            }

            $this->transactions = max(0, $this->transactions - 1);
        } catch (Throwable $e) {
            $this->transactions = 0;
            throw new TransactionException(
                message     : "Failed to rollback transaction: " . $e->getMessage(),
                nestingLevel: $this->transactions,
                previous    : $e
            );
        }
    }

    /**
     * Create a named savepoint within the current transaction.
     *
     * -- intent: enable partial rollback capability for nested operations.
     *
     * @param string $name Savepoint identifier
     *
     * @return void
     * @throws TransactionException If savepoint creation fails
     */
    public function savepoint(string $name) : void
    {
        $this->connection->getConnection()->exec(statement: "SAVEPOINT {$name}");
    }

    /**
     * Rollback to a previously created savepoint.
     *
     * -- intent: recover from errors within a transaction without full rollback.
     *
     * @param string $name Savepoint identifier
     *
     * @return void
     * @throws TransactionException If rollback to savepoint fails
     */
    public function rollbackTo(string $name) : void
    {
        $this->connection->getConnection()->exec(statement: "ROLLBACK TO {$name}");
    }
}
