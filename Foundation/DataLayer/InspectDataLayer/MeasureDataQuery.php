<?php

declare(strict_types=1);

namespace Avax\DataLayer\InspectDataLayer;

final readonly class MeasureDataQuery
{
    public function __construct(
        private DataQueryMeasurement $measurement
    ) {}

    public function describeResponsibility() : string
    {
        return 'measures data query execution time, rows, and resource usage.';
    }

    public function measure(callable $query) : DataQueryMeasurement
    {
        $start = microtime(true);

        try {
            $result   = $query();
            $duration = (microtime(true) - $start) * 1000;

            return DataQueryMeasurement::create($duration, 'SELECT');
        } catch (\Throwable $e) {
            $duration = (microtime(true) - $start) * 1000;

            return new DataQueryMeasurement(
                durationMs  : $duration,
                queryType   : 'SELECT',
                rowsAffected: 0,
                error       : $e->getMessage()
            );
        }
    }

    public function toMetadata() : array
    {
        return [];
    }
}