<?php

declare(strict_types=1);

namespace Avax\DataLayer\CommitDataChanges;

/**
 * CoordinateTwoPhaseCommit - coordinates two-phase commit only when an advanced distributed policy asks for it.
 */
final readonly class CoordinateTwoPhaseCommit
{
    public function describeResponsibility() : string
    {
        return 'coordinates two-phase commit only when an advanced distributed policy asks for it.';
    }
}
