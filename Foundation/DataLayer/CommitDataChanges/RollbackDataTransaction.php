<?php

declare(strict_types=1);

namespace Avax\DataLayer\CommitDataChanges;

/**
 * RollbackDataTransaction - marks an open transaction as rolled back.
 */
final readonly class RollbackDataTransaction
{
    public function rollback(DataTransaction $transaction) : DataTransaction
    {
        if ($transaction->status !== 'open') {
            throw DataTransactionFailure::alreadyClosed(status: $transaction->status);
        }

        return $transaction->markRolledBack();
    }
}
