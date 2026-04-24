<?php

declare(strict_types=1);

namespace Avax\DataLayer\QueryStoredData;

/**
 * DataQueryPlan - records the compiled execution plan for a query.
 */
final readonly class DataQueryPlan
{
    public function describeResponsibility() : string
    {
        return 'records the compiled execution plan for a query.';
    }
}
