<?php

declare(strict_types=1);

namespace Avax\DataLayer\CommitDataChanges;

/**
 * CommitPreparedDataChange - commits a prepared distributed data change.
 */
final readonly class CommitPreparedDataChange
{
    public function describeResponsibility() : string
    {
        return 'commits a prepared distributed data change.';
    }
}
