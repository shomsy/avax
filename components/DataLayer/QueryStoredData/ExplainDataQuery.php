<?php

declare(strict_types=1);

namespace components\DataLayer\QueryStoredData;

/**
 * ExplainDataQuery - explains a query path before execution.
 */
final readonly class ExplainDataQuery
{
    public function describeResponsibility() : string
    {
        return 'explains a query path before execution.';
    }
}
