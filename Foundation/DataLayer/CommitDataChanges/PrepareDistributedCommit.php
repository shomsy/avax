<?php

declare(strict_types=1);

namespace Avax\DataLayer\CommitDataChanges;

/**
 * PrepareDistributedCommit - prepares a distributed data change before commit.
 */
final readonly class PrepareDistributedCommit
{
    public function describeResponsibility() : string
    {
        return 'prepares a distributed data change before commit.';
    }
}
