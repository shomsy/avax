<?php

declare(strict_types=1);

namespace Avax\DataLayer\InspectDataLayer;

final readonly class DataQueryMeasurement
{
    public function __construct(
        public float  $durationMs,
        public string $queryType,
        public int    $rowsAffected,
        public string $error
    ) {}

    public static function create(float $durationMs, string $queryType = 'SELECT') : self
    {
        return new self(
            durationMs  : $durationMs,
            queryType   : $queryType,
            rowsAffected: 0,
            error       : ''
        );
    }

    public function describeResponsibility() : string
    {
        return 'records measured query duration.';
    }

    public function isSlow(float $thresholdMs = 1000) : bool
    {
        return $this->durationMs > $thresholdMs;
    }

    public function toMetadata() : array
    {
        return [
            'duration_ms'   => $this->durationMs,
            'query_type'    => $this->queryType,
            'rows_affected' => $this->rowsAffected,
            'error'         => $this->error,
        ];
    }
}