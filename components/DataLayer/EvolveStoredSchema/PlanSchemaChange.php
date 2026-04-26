<?php

declare(strict_types=1);

namespace components\DataLayer\EvolveStoredSchema;

/**
 * PlanSchemaChange - plans a schema change before execution.
 */
final readonly class PlanSchemaChange
{
    public function describeResponsibility() : string
    {
        return 'plans a schema change before execution.';
    }
}
