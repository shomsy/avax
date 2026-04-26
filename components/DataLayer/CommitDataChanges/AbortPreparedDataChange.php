<?php

declare(strict_types=1);

namespace Avax\DataLayer\CommitDataChanges;

/**
 * AbortPreparedDataChange - aborts a prepared distributed data change.
 */
final readonly class AbortPreparedDataChange
{
    public function describeResponsibility() : string
    {
        return 'aborts a prepared distributed data change.';
    }
}
