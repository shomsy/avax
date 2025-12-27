<?php

declare(strict_types=1);

namespace Avax\Database\Transaction;

use Avax\Database\Transaction\Contracts\TransactionManagerInterface;
use Throwable;

/**
 * Technical RAII-style scope value object for managing the lifetime of a specific transaction block.
 *
 * -- intent:
 * Implements the "Dispose/RAII" pattern to ensure deterministic transaction 
 * finalization. This allows developers to open a transaction window that 
 * automatically reverts (ROLLBACK) upon destruction if an explicit 
 * completion (COMMIT) signal was not dispatched, safeguarding against 
 * dangling transactions.
 *
 * -- invariants:
 * - Must instantiate a new transaction window upon construction.
 * - Must perform an automated ROLLBACK in the destructor if 'complete()' was not triggered.
 * - 'complete()' must be final and prevent subsequent automated rollbacks.
 *
 * -- boundaries:
 * - Does NOT perform the technical persistence operations (delegated to Manager).
 * - Acts strictly as a lifecycle guardian for the transaction window.
 */
final class TransactionScope
{
    /** @var bool Logical flag indicating if the technical transaction window has been finalized/committed. */
    private bool $completed = false;

    /**
     * @param TransactionManagerInterface $manager The active technical authority responsible for atomicity and persistence.
     */
    public function __construct(private readonly TransactionManagerInterface $manager)
    {
        $this->manager->begin();
    }

    /**
     * Coordinate the automated teardown and defensive rollback of a dangling transaction window.
     *
     * -- intent:
     * Prevents data corruption and persistent connection locks by ensuring 
     * that every transaction opened by this scope is closed, either through 
     * success (COMMIT) or defensive failure recovery (ROLLBACK).
     */
    public function __destruct()
    {
        if (! $this->completed) {
            try {
                $this->manager->rollback();
            } catch (Throwable) {
                // Defensive: isolation of secondary destruction failures to prevent process termination.
            }
        }
    }

    /**
     * Coordinate the manual finalization and technical COMMIT of all changes within this scope.
     *
     * -- intent:
     * Signals the successful and complete execution of the enclosed unit 
     * of work, instructing the manager to persist changes and disabling the 
     * automated rollback guardian.
     *
     * @return void
     */
    public function complete(): void
    {
        $this->manager->commit();
        $this->completed = true;
    }
}
