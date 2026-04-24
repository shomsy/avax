<?php

declare(strict_types=1);

namespace Avax\DataLayer\QueryStoredData;

/**
 * DataQuery - records query intent independently from a database builder implementation.
 */
final readonly class DataQuery
{
    public function describeResponsibility() : string
    {
        return 'records query intent independently from a database builder implementation.';
    }
}
