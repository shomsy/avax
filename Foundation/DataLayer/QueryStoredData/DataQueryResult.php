<?php

declare(strict_types=1);

namespace Avax\DataLayer\QueryStoredData;

/**
 * DataQueryResult - records query output without requiring full object hydration.
 */
final readonly class DataQueryResult
{
    public function describeResponsibility() : string
    {
        return 'records query output without requiring full object hydration.';
    }
}
