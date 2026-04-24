<?php

declare(strict_types=1);

namespace Avax\DataLayer\CommitDataChanges;

/**
 * ChooseIsolationLevel - makes the transaction isolation level a visible policy decision.
 */
final readonly class ChooseIsolationLevel
{
    public function choose(DataTransactionPolicy $policy) : IsolationLevel
    {
        return $policy->isolationLevel;
    }
}
