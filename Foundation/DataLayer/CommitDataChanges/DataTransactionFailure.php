<?php

declare(strict_types=1);

namespace Avax\DataLayer\CommitDataChanges;

use RuntimeException;

/**
 * DataTransactionFailure - reports unsafe transaction retry and invalid transaction state.
 */
final class DataTransactionFailure extends RuntimeException
{
    public static function unsafeRetryPolicy() : self
    {
        return new self('Transient retry is allowed only for idempotent data work. Mark the policy idempotent or disable retry.');
    }

    public static function alreadyClosed(string $status) : self
    {
        return new self("Transaction cannot be changed because it is already {$status}.");
    }
}
