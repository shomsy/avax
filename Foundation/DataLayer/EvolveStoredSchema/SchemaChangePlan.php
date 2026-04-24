<?php

declare(strict_types=1);

namespace Avax\DataLayer\EvolveStoredSchema;

/**
 * SchemaChangePlan - records intended schema operations and rollback expectations.
 */
final readonly class SchemaChangePlan
{
    public function describeResponsibility() : string
    {
        return 'records intended schema operations and rollback expectations.';
    }
}
