<?php

declare(strict_types=1);

namespace Avax\DataLayer\EvolveStoredSchema;

/**
 * DetectSchemaDrift - detects differences between intended schema and runtime schema.
 */
final readonly class DetectSchemaDrift
{
    public function describeResponsibility() : string
    {
        return 'detects differences between intended schema and runtime schema.';
    }
}
