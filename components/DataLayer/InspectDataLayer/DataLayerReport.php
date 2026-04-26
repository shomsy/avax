<?php

declare(strict_types=1);

namespace Avax\DataLayer\InspectDataLayer;

final readonly class DataLayerReport
{
    public function __construct(
        public string $periodStart,
        public string $periodEnd,
        public array  $events,
        public array  $measurements,
        public array  $fingerprints
    ) {}

    public static function create(array $events, array $measurements) : self
    {
        return new self(
            periodStart : date('c', strtotime('-1 day')),
            periodEnd   : date('c'),
            events      : $events,
            measurements: $measurements,
            fingerpings : []
        );
    }

    public function describeResponsibility() : string
    {
        return 'generates data layer report for monitoring and analysis.';
    }

    public function summary() : array
    {
        return [
            'total_events'  => count($this->events),
            'total_queries' => count($this->measurements),
            'period_start'  => $this->periodStart,
            'period_end'    => $this->periodEnd,
        ];
    }

    public function toMetadata() : array
    {
        return [
            'period_start'      => $this->periodStart,
            'period_end'        => $this->periodEnd,
            'event_count'       => count($this->events),
            'measurement_count' => count($this->measurements),
        ];
    }
}