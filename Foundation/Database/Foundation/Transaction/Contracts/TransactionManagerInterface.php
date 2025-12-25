<?php

declare(strict_types=1);

namespace Avax\Database\Transaction\Contracts;

use Throwable;

/**
 * Defines the essential contract for coordinating atomic database transactions.
 *
 * -- intent: provide a standard API for starting, committing, and rolling back operations.
 */
interface TransactionManagerInterface
{
    /**
     * Execute a domain closure within a managed database transaction.
     *
     * -- intent: provide automatic atomicity and rollback for the provided logic.
     *
     * @param callable $callback The unit of work to execute
     *
     * @return mixed
     * @throws Throwable If the operation fails after multiple attempts
     */
    public function transaction(callable $callback) : mixed;

    /**
     * Physically begin a new transaction on the database driver.
     *
     * -- intent: signal the start of a protected sequence of operations.
     *
     * @return void
     */
    public function begin() : void;

    /**
     * Permanently persist all changes made within the current transaction.
     *
     * -- intent: finalize the atomic sequence and commit data to storage.
     *
     * @return void
     */
    public function commit() : void;

    /**
     * Revert all changes made during the active transaction.
     *
     * -- intent: protect data integrity by discarding failed or partial operations.
     *
     * @return void
     */
    public function rollback() : void;
}
