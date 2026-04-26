<?php

declare(strict_types=1);

namespace Avax\DataLayer\AccelerateDataReads;

enum SlowQuerySeverity: string
{
    case NONE     = 'none';
    case NOTICE   = 'notice';
    case WARNING  = 'warning';
    case CRITICAL = 'critical';
}

final readonly class SlowDataQueryReport
{
    public function __construct(
        public bool              $isSlow,
        public float             $executionTimeMs,
        public int               $thresholdMs,
        public int               $rowsExamined,
        public int               $rowsReturned,
        public SlowQuerySeverity $severity,
        public string|null       $queryFingerprint
    ) {}

    public function describeResponsibility() : string
    {
        return 'reports slow query detection results with execution time, rows examined, and severity.';
    }

    public function isCritical() : bool
    {
        return $this->severity === SlowQuerySeverity::CRITICAL;
    }

    public function toMetadata() : array
    {
        return [
            'is_slow'           => $this->isSlow,
            'execution_time_ms' => $this->executionTimeMs,
            'threshold_ms'      => $this->thresholdMs,
            'rows_examined'     => $this->rowsExamined,
            'rows_returned'     => $this->rowsReturned,
            'cost_ratio'        => $this->costRatio(),
            'severity'          => $this->severity->value,
            'query_fingerprint' => $this->queryFingerprint,
        ];
    }

    public function costRatio() : float
    {
        return $this->rowsReturned > 0
            ? $this->rowsExamined / $this->rowsReturned
            : (float) $this->rowsExamined;
    }
}