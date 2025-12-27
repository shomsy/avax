<?php

declare(strict_types=1);

namespace Avax\Database\Transaction\Contracts;

use Avax\Database\Connection\Contracts\DatabaseConnection;
use Throwable;

/**
 * technical contract defining the authoritative capabilities for coordinating atomic database transactions.
 *
 * -- intent:
 * Establishes a dialect-neutral, consistent interface for managing the
 * lifecycle of atomic database operations, ensuring that ACID properties
 * can be enforced across the entire system persistence layer.
 *
 * -- invariants:
 * - Implementation must maintain transactional depth (nesting) to prevent premature commits.
 * - Every manual transition (Begin/Commit/Rollback) must return the manager for fluent chaining.
 * - Callback-based transaction management must provide automated recovery.
 *
 * -- boundaries:
 * - Does NOT handle SQL compilation or result projection (QueryBuilder domain).
 * - Depends on the DatabaseConnection contract for driver-level negotiations.
 */
interface TransactionManagerInterface
{
    /**
     * Coordinate the execution of a professional technical closure within a managed database transaction.
     *
     * -- intent:
     * Provides a high-level orchestration for executing a unit of work that
     * requires strict atomicity, ensuring automated ROLLBACK upon failure
     * and COMMIT upon success.
     *
     * @param callable $callback The technical logic (unit of work) to be executed within the protected scope.
     *
     * @return mixed The scalar or composite result returned by the provided callback.
     * @throws Throwable If the technical transaction management or callback execution fails.
     */
    public function transaction(callable $callback) : mixed;

    /**
     * Physically signal the start of a fresh technical transaction window on the database driver.
     *
     * -- intent:
     * Instructs the persistence engine to open a protected session window,
     * buffering subsequent mutations until a finalization signal is received.
     *
     * @return TransactionManagerInterface The current manager instance for continued fluent configuration.
     */
    public function begin() : TransactionManagerInterface;

    /**
     * Coordinate the permanent persistence of all technical changes made within the current transaction window.
     *
     * -- intent:
     * Instructs the persistence engine to finalize the atomic sequence and
     * permanently commit all buffered mutations to non-volatile storage.
     *
     * @return TransactionManagerInterface The current manager instance.
     */
    public function commit() : TransactionManagerInterface;

    /**
     * Coordinate the technical reversion of all changes made during the active transaction window.
     *
     * -- intent:
     * Discards all buffered mutations and restores the database state to the
     * moment the current transaction window was initiated, safeguarding data integrity.
     *
     * @return TransactionManagerInterface The current manager instance.
     */
    public function rollback() : TransactionManagerInterface;

    /**
     * Retrieve the authorized technical connection gateway managed by this authority.
     *
     * -- intent:
     * Provides authorized access to the technical connection instance that
     * is currently bound to the transaction lifecycle.
     *
     * @return DatabaseConnection The active transaction-bound persistence gateway.
     */
    public function getConnection() : DatabaseConnection;
}
