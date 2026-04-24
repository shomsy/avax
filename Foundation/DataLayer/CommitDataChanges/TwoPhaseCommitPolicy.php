<?php

declare(strict_types=1);

namespace Avax\DataLayer\CommitDataChanges;

/**
 * TwoPhaseCommitPolicy - declares that two-phase commit is an advanced opt-in policy, not the default.
 */
final readonly class TwoPhaseCommitPolicy
{
    public function describeResponsibility() : string
    {
        return 'declares that two-phase commit is an advanced opt-in policy, not the default.';
    }
}
