<?php

declare(strict_types=1);

namespace Avax\DataLayer\AccelerateDataReads;

use InvalidArgumentException;

final readonly class DetectSlowDataQuery
{
    private int $thresholdMs;

    public function __construct(int $thresholdMs = 1000)
    {
        if ($thresholdMs < 0) {
            throw new InvalidArgumentException(message: 'Threshold must be non-negative.');
        }
        $this->thresholdMs = $thresholdMs;
    }

    public function describeResponsibility() : string
    {
        return 'detects slow queries against a threshold.';
    }

    public function check(array $queryMetrics) : SlowDataQueryReport
    {
        $executionTime = $queryMetrics['execution_time_ms'] ?? 0.0;
        $rowsExamined  = $queryMetrics['rows_examined'] ?? 0;
        $rowsReturned  = $queryMetrics['rows_returned'] ?? 0;

        $isSlow   = $this->isSlow(executionTimeMs: $executionTime);
        $severity = $this->calculateSeverity(executionTime: $executionTime, rowsExamined: $rowsExamined, rowsReturned: $rowsReturned);

        return new SlowDataQueryReport(
            isSlow          : $isSlow,
            executionTimeMs : $executionTime,
            thresholdMs     : $this->thresholdMs,
            rowsExamined    : $rowsExamined,
            rowsReturned    : $rowsReturned,
            severity        : $severity,
            queryFingerprint: $queryMetrics['query_fingerprint'] ?? null
        );
    }

    public function isSlow(float $executionTimeMs) : bool
    {
        return $executionTimeMs > $this->thresholdMs;
    }

    private function calculateSeverity(float $executionTime, int $rowsExamined, int $rowsReturned) : string
    {
        $ratio = $rowsReturned > 0 ? $rowsExamined / $rowsReturned : PHP_INT_MAX;

        if ($executionTime > $this->thresholdMs * 5 || $ratio > 1000) {
            return 'critical';
        }
        if ($executionTime > $this->thresholdMs * 2 || $ratio > 100) {
            return 'warning';
        }
        if ($this->isSlow(executionTimeMs: $executionTime)) {
            return 'notice';
        }

        return 'none';
    }

    public function withThreshold(int $thresholdMs) : self
    {
        return new self(thresholdMs: $thresholdMs);
    }

    public function toMetadata() : array
    {
        return ['threshold_ms' => $this->thresholdMs];
    }
}