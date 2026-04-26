<?php

declare(strict_types=1);

namespace components\DataLayer\EvolveStoredSchema;

/**
 * ZeroDowntimeMigrationPlan - records the online migration path.
 */
final readonly class ZeroDowntimeMigrationPlan
{
    public function describeResponsibility() : string
    {
        return 'records the online migration path.';
    }
}
