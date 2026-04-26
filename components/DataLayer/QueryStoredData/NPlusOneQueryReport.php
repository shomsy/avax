<?php

declare(strict_types=1);

namespace components\DataLayer\QueryStoredData;

/**
 * NPlusOneQueryReport - records N+1 evidence and where it appeared.
 */
final readonly class NPlusOneQueryReport
{
    public function describeResponsibility() : string
    {
        return 'records N+1 evidence and where it appeared.';
    }
}
