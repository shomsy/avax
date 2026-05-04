<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\Diagnostics;

use function count;

/**
 * Statistics about slow persistence operations.
 */
final readonly class SlowPersistenceStatistics
{
    public function __construct(
        public int   $totalSlowOperations = 0,
        public float $totalDurationMs = 0.0,
        public float $averageDurationMs = 0.0,
        public float $maxDurationMs = 0.0,
        public float $minDurationMs = 0.0,
        public array $countsByType = [],
        public array $countsByOperation = [],
        public array $countsByFingerprint = [],
    )
    {
    }

    /**
     * Creates statistics from a list of slow persistence reports.
     *
     * @param list<SlowPersistenceReport> $reports
     */
    public static function fromReports(array $reports): self
    {
        if ($reports === []) {
            return new self();
        }

        $totalDuration = 0.0;
        $maxDuration = 0.0;
        $minDuration = PHP_FLOAT_MAX;
        $byType = [];
        $byOperation = [];
        $byFingerprint = [];

        foreach ($reports as $report) {
            $totalDuration += $report->durationMs;

            if ($report->durationMs > $maxDuration) {
                $maxDuration = $report->durationMs;
            }

            if ($report->durationMs < $minDuration) {
                $minDuration = $report->durationMs;
            }

            $byType[$report->type] = ($byType[$report->type] ?? 0) + 1;
            $byOperation[$report->operation] = ($byOperation[$report->operation] ?? 0) + 1;
            $byFingerprint[$report->fingerprint] = ($byFingerprint[$report->fingerprint] ?? 0) + 1;
        }

        $count = count($reports);

        return new self(
            totalSlowOperations: $count,
            totalDurationMs: $totalDuration,
            averageDurationMs: $totalDuration / $count,
            maxDurationMs: $maxDuration,
            minDurationMs: $minDuration,
            countsByType: $byType,
            countsByOperation: $byOperation,
            countsByFingerprint: $byFingerprint,
        );
    }

    /**
     * Returns a summary string.
     */
    public function summary(): string
    {
        return sprintf(
            "Slow Persistence Statistics:\n"
            . "  Total: %d\n"
            . "  Avg: %.2fms\n"
            . "  Max: %.2fms\n"
            . "  Min: %.2fms\n"
            . "  Total Duration: %.2fms\n"
            . "  By Type: %s\n"
            . '  By Operation: %s',
            $this->totalSlowOperations,
            $this->averageDurationMs,
            $this->maxDurationMs,
            $this->minDurationMs,
            $this->totalDurationMs,
            json_encode($this->countsByType, JSON_THROW_ON_ERROR),
            json_encode($this->countsByOperation, JSON_THROW_ON_ERROR),
        );
    }
}
