<?php

declare(strict_types=1);

namespace Avax\DataLayer\InspectDataLayer;

final readonly class InspectDataLayer
{
    public function __construct(
        private DataLayerEvent       $event,
        private DataQueryMeasurement $measurement,
        private DataQueryFingerprint $fingerprint,
        private TraceDataTransaction $trace,
        private BuildDataLayerReport $report
    ) {}

    public function inspect(array $context) : DataLayerInspectionResult
    {
        return new DataLayerInspectionResult(
            events      : [],
            measurements: [],
            fingerprints: [],
            traces      : [],
            generatedAt : microtime(true)
        );
    }

    public function toMetadata() : array
    {
        return [
            'event_recording'   => $this->event->describeResponsibility(),
            'query_measurement' => $this->measurement->describeResponsibility(),
            'fingerprinting'    => $this->fingerprint->describeResponsibility(),
            'tracing'           => $this->trace->describeResponsibility(),
            'reporting'         => $this->report->describeResponsibility(),
        ];
    }

    public function describeResponsibility() : string
    {
        return 'groups event recording, query measurement, fingerprinting, transaction tracing, and report building.';
    }
}

final readonly class DataLayerInspectionResult
{
    public function __construct(
        public array $events,
        public array $measurements,
        public array $fingerprints,
        public array $traces,
        public float $generatedAt
    ) {}
}