<?php

declare(strict_types=1);

namespace Avax\DataLayer\EvolveStoredSchema;

/**
 * SchemaChangeRisk - records migration risk classification.
 */
final readonly class SchemaChangeRisk
{
    public function describeResponsibility() : string
    {
        return 'records migration risk classification.';
    }
}
