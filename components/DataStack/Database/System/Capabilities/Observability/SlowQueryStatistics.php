<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Observability;

/**
 * Statistics about slow queries detected over a period.
 */
final readonly class SlowQueryStatistics
{
    public function __construct(
        public int $totalSlowQueries = 0,
        public float $totalDurationMs = 0.0,
        public float $averageDurationMs = 0.0,
        public float $maxDurationMs = 0.0,
        public float $minDurationMs = 0.0,
        public array $countsByFingerprint = [],
        public array $countsByConnection = [],
    ) {
    }

    /**
     * Creates statistics from a list of slow query reports.
     *
     * @param  list<SlowQueryReport>  $reports
     */
    public static function fromReports(array $reports): self
    {
        if ($reports === []) {
            return new self();
        }

        $totalDuration = 0.0;
        $maxDuration = 0.0;
        $minDuration = PHP_FLOAT_MAX;
        $byFingerprint = [];
        $byConnection = [];

        foreach ($reports as $report) {
            $totalDuration += $report->durationMs;

            if ($report->durationMs > $maxDuration) {
                $maxDuration = $report->durationMs;
            }

            if ($report->durationMs < $minDuration) {
                $minDuration = $report->durationMs;
            }

            if ($report->fingerprint !== '') {
                $fp = $report->fingerprint;
                $byFingerprint[$fp] = ($byFingerprint[$fp] ?? 0) + 1;
            }

            if ($report->connection !== '') {
                $conn = $report->connection;
                $byConnection[$conn] = ($byConnection[$conn] ?? 0) + 1;
            }
        }

        $count = count($reports);

        return new self(
            totalSlowQueries: $count,
            totalDurationMs: $totalDuration,
            averageDurationMs: $totalDuration / $count,
            maxDurationMs: $maxDuration,
            minDurationMs: $minDuration,
            countsByFingerprint: $byFingerprint,
            countsByConnection: $byConnection,
        );
    }

    /**
     * Returns a summary string.
     */
    public function summary(): string
    {
        return sprintf(
            "Slow Query Statistics:\n  Total: %d\n  Avg: %.2fms\n  Max: %.2fms\n  Min: %.2fms\n  Total Duration: %.2fms",
            $this->totalSlowQueries,
            $this->averageDurationMs,
            $this->maxDurationMs,
            $this->minDurationMs,
            $this->totalDurationMs,
        );
    }
}
