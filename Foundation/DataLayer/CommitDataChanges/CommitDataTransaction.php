<?php

declare(strict_types=1);

namespace Avax\DataLayer\CommitDataChanges;

/**
 * CommitDataTransaction - marks an open transaction as committed.
 */
final readonly class CommitDataTransaction
{
    public function commit(DataTransaction $transaction) : DataTransaction
    {
        if ($transaction->status !== 'open') {
            throw DataTransactionFailure::alreadyClosed(status: $transaction->status);
        }

        return $transaction->markCommitted();
    }
}
