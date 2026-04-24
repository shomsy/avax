<?php

declare(strict_types=1);

namespace Avax\DataLayer\CommitDataChanges;

/**
 * OpenDataTransaction - creates an explicit transaction marker from a transaction policy.
 */
final readonly class OpenDataTransaction
{
    public function open(DataTransactionPolicy|null $policy = null) : DataTransaction
    {
        return new DataTransaction(
            id    : bin2hex(string: random_bytes(length: 8)),
            policy: $policy ?? new DataTransactionPolicy()
        );
    }
}
