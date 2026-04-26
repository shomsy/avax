<?php

declare(strict_types=1);

namespace components\DataLayer\EvolveStoredSchema;

/**
 * RollbackSchemaChange - runs the declared rollback path for a schema change.
 */
final readonly class RollbackSchemaChange
{
    public function describeResponsibility() : string
    {
        return 'runs the declared rollback path for a schema change.';
    }
}
