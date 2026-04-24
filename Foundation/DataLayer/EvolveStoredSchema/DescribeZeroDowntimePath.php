<?php

declare(strict_types=1);

namespace Avax\DataLayer\EvolveStoredSchema;

/**
 * DescribeZeroDowntimePath - describes the expand-migrate-contract path for online schema changes.
 */
final readonly class DescribeZeroDowntimePath
{
    public function describeResponsibility() : string
    {
        return 'describes the expand-migrate-contract path for online schema changes.';
    }
}
