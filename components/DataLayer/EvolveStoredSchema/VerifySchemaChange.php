<?php

declare(strict_types=1);

namespace components\DataLayer\EvolveStoredSchema;

/**
 * VerifySchemaChange - verifies a schema change plan before it can run.
 */
final readonly class VerifySchemaChange
{
    public function describeResponsibility() : string
    {
        return 'verifies a schema change plan before it can run.';
    }
}
