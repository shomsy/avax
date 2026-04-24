<?php

declare(strict_types=1);

namespace Avax\DataLayer\EvolveStoredSchema;

/**
 * ApplySchemaChange - applies a verified schema change plan.
 */
final readonly class ApplySchemaChange
{
    public function describeResponsibility() : string
    {
        return 'applies a verified schema change plan.';
    }
}
