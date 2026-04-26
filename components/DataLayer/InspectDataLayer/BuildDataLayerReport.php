<?php

declare(strict_types=1);

namespace Avax\DataLayer\InspectDataLayer;

final readonly class BuildDataLayerReport
{
    private DataLayerReport $report;

    public function describeResponsibility() : string
    {
        return 'builds data layer report from events, measurements, and fingerprints.';
    }

    public function build(array $events, array $measurements) : DataLayerReport
    {
        return DataLayerReport::create(events: $events, measurements: $measurements);
    }

    public function toMetadata() : array
    {
        return [];
    }
}