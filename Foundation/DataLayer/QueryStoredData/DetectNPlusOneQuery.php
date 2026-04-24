<?php

declare(strict_types=1);

namespace Avax\DataLayer\QueryStoredData;

/**
 * DetectNPlusOneQuery - detects query repetition that indicates an N+1 path.
 */
final readonly class DetectNPlusOneQuery
{
    public function describeResponsibility() : string
    {
        return 'detects query repetition that indicates an N+1 path.';
    }
}
