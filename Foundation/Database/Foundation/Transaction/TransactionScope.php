<?php

declare(strict_types=1);

namespace Avax\Database\Transaction;

use Avax\Database\Transaction\Contracts\TransactionManagerInterface;
use Throwable;

/**
 * Technical value object for managing the lifetime of a specific transaction block.
 *
 * -- intent: implement the Dispose pattern for automatic transaction finalization.
 */
final readonly class TransactionScope
{
    /**
     * Constructor promoting the transaction manager dependency.
     *
     * -- intent: capture the active manager and start a new transaction sequence.
     *
     * @param TransactionManagerInterface $manager The active technician for atomicity
     */
    public function __construct(private TransactionManagerInterface $manager)
    {
        $this->manager->begin();
    }

    /**
     * Destructor ensuring the transaction is closed if not manually committed.
     *
     * -- intent: safeguard data integrity by rolling back dangling transactions.
     */
    public function __destruct()
    {
        try {
            $this->manager->rollback();
        } catch (Throwable) {
            // Suppress secondary failures during destruction
        }
    }

    /**
     * Manually finalize and commit the changes in this scope.
     *
     * -- intent: signal the successful completion of the scoped work.
     *
     * @return void
     */
    public function complete() : void
    {
        $this->manager->commit();
    }
}


