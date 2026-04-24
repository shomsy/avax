<?php

declare(strict_types=1);

namespace Avax\DataLayer\EvolveStoredSchema;

/**
 * SchemaDriftReport - records detected schema drift and its operational impact.
 */
final readonly class SchemaDriftReport
{
    public function describeResponsibility() : string
    {
        return 'records detected schema drift and its operational impact.';
    }
}
