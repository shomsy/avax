<?php

declare(strict_types=1);

namespace Avax\DataLayer\QueryStoredData;

/**
 * QueryStoredData - groups query build, validation, compile, execute, explain, and N+1 detection responsibilities.
 */
final readonly class QueryStoredData
{
    public function describeResponsibility() : string
    {
        return 'groups query build, validation, compile, execute, explain, and N+1 detection responsibilities.';
    }
}
