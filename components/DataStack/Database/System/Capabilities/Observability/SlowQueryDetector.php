<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Observability;

/**
 * A report for a detected slow query.
 *
 * Readonly value object containing information about a query
 * that exceeded the slow query threshold.
 */
final readonly class SlowQueryReport
{
    public function __construct(
        public string $sql,
        public array $bindings = [],
        public float $durationMs = 0.0,
        public float $thresholdMs = 0.0,
        public string $fingerprint = '',
        public string $connection = '',
        public float $timestamp = 0.0,
        public int $occurrences = 1,
    ) {}

    /**
     * Returns a human-readable summary.
     */
    public function summary(): string
    {
        return sprintf(
            "[%s] Query took %.2fms (threshold: %.2fms, %.1fx over)\nSQL: %s",
            $this->severityLabel(),
            $this->durationMs,
            $this->thresholdMs,
            $this->timesOverThreshold(),
            substr($this->sql, 0, 200),
        );
    }

    /**
     * Returns a formatted duration comparison string.
     */
    public function severityLabel(): string
    {
        $ratio = $this->timesOverThreshold();

        return match (true) {
            $ratio >= 10.0 => 'CRITICAL',
            $ratio >= 5.0 => 'SEVERE',
            $ratio >= 2.0 => 'WARNING',
            default       => 'SLOW',
        };
    }

    /**
     * Returns how many times the threshold was exceeded.
     */
    public function timesOverThreshold(): float
    {
        if ($this->thresholdMs <= 0) {
            return 0.0;
        }

        return $this->durationMs / $this->thresholdMs;
    }

    /**
     * Converts to an associative array.
     */
    public function toArray(): array
    {
        return [
            'sql'         => $this->sql,
            'bindings'    => $this->bindings,
            'duration_ms' => $this->durationMs,
            'threshold_ms' => $this->thresholdMs,
            'fingerprint' => $this->fingerprint,
            'connection'  => $this->connection,
            'timestamp'   => $this->timestamp,
            'occurrences' => $this->occurrences,
            'severity'    => $this->severityLabel(),
            'times_over'  => $this->timesOverThreshold(),
        ];
    }
}

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
    ) {}

    /**
     * Creates statistics from a list of slow query reports.
     *
     * @param list<SlowQueryReport> $reports
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
            totalSlowQueries   : $count,
            totalDurationMs    : $totalDuration,
            averageDurationMs  : $totalDuration / $count,
            maxDurationMs      : $maxDuration,
            minDurationMs      : $minDuration,
            countsByFingerprint: $byFingerprint,
            countsByConnection : $byConnection,
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

/**
 * Detects and tracks slow queries that exceed a configured threshold.
 *
 * Maintains a slow query log and provides statistics for analysis.
 * Works with the QueryTimeline to identify problematic queries.
 */
final class SlowQueryDetector
{
    /**
     * @var list<SlowQueryReport> The slow query log
     */
    private array $slowQueries = [];

    /**
     * @var array<string, int> Count of slow queries per fingerprint
     */
    private array $fingerprintCounts = [];

    public function __construct(
        /**
         * @var float Threshold in milliseconds for what constitutes a "slow" query
         */
        private float $thresholdMs = 1000.0,
        /**
         * @var QueryFingerprinter|null Optional fingerprinter for grouping
         */
        private readonly ?QueryFingerprinter $queryFingerprinter = null,
        /**
         * @var int Maximum number of slow queries to retain (0 = unlimited)
         */
        private readonly int $maxEntries = 0,
    ) {}

    /**
     * Analyzes all entries from a QueryTimeline and detects slow queries.
     *
     * @return list<SlowQueryReport>
     */
    public function analyzeTimeline(QueryTimeline $queryTimeline): array
    {
        $detected = [];

        foreach ($queryTimeline->all() as $queryEntry) {
            if ($this->recordEntry($queryEntry)) {
                $detected[] = end($this->slowQueries);
            }
        }

        return $detected;
    }

    /**
     * Returns all recorded slow queries.
     *
     * @return list<SlowQueryReport>
     */
    public function all(): array
    {
        return $this->slowQueries;
    }

    /**
     * Records a query entry from the QueryTimeline.
     */
    public function recordEntry(QueryEntry $queryEntry): bool
    {
        return $this->record(
            sql       : $queryEntry->sql,
            durationMs: $queryEntry->durationMs,
            bindings  : $queryEntry->bindings,
            connection: $queryEntry->connection,
        );
    }

    /**
     * Records a query execution and checks if it's slow.
     *
     * @return bool Whether the query was classified as slow
     */
    public function record(
        string $sql,
        float $durationMs,
        array $bindings = [],
        string $connection = '',
    ): bool {
        if ($durationMs < $this->thresholdMs) {
            return false;
        }

        $fingerprint = '';
        if ($this->queryFingerprinter !== null) {
            $fingerprint = $this->queryFingerprinter->fingerprint($sql)->hash;
        }

        $slowQueryReport = new SlowQueryReport(
            sql        : $sql,
            bindings   : $bindings,
            durationMs : $durationMs,
            thresholdMs: $this->thresholdMs,
            fingerprint: $fingerprint,
            connection : $connection,
            timestamp  : microtime(true),
            occurrences: 1,
        );

        $this->addReport($slowQueryReport);

        return true;
    }

    /**
     * Adds a slow query report directly.
     */
    public function addReport(SlowQueryReport $slowQueryReport): void
    {
        $this->slowQueries[] = $slowQueryReport;

        if ($slowQueryReport->fingerprint !== '') {
            $this->fingerprintCounts[$slowQueryReport->fingerprint]
                = ($this->fingerprintCounts[$slowQueryReport->fingerprint] ?? 0) + 1;
        }

        // Enforce max entries
        if ($this->maxEntries > 0 && count($this->slowQueries) > $this->maxEntries) {
            array_shift($this->slowQueries);
        }
    }

    /**
     * Returns slow queries filtered by fingerprint.
     *
     * @return list<SlowQueryReport>
     */
    public function byFingerprint(string $fingerprint): array
    {
        return array_values(array_filter(
            $this->slowQueries,
            static fn (SlowQueryReport $slowQueryReport): bool => $slowQueryReport->fingerprint === $fingerprint,
        ));
    }

    /**
     * Returns slow queries filtered by severity label.
     *
     * @return list<SlowQueryReport>
     */
    public function bySeverity(string $severity): array
    {
        return array_values(array_filter(
            $this->slowQueries,
            static fn (SlowQueryReport $slowQueryReport): bool => $slowQueryReport->severityLabel() === $severity,
        ));
    }

    /**
     * Returns the N slowest queries.
     *
     * @return list<SlowQueryReport>
     */
    public function topSlow(int $limit = 10): array
    {
        $sorted = $this->slowQueries;
        usort(
            $sorted,
            static fn (SlowQueryReport $a, SlowQueryReport $b): int => $b->durationMs <=> $a->durationMs,
        );

        return array_slice($sorted, 0, $limit);
    }

    /**
     * Returns computed statistics about slow queries.
     */
    public function statistics(): SlowQueryStatistics
    {
        return SlowQueryStatistics::fromReports($this->slowQueries);
    }

    /**
     * Returns the count of slow queries per fingerprint.
     *
     * @return array<string, int>
     */
    public function fingerprintCounts(): array
    {
        return $this->fingerprintCounts;
    }

    /**
     * Returns the total count of slow queries.
     */
    public function count(): int
    {
        return count($this->slowQueries);
    }

    /**
     * Returns the current threshold in milliseconds.
     */
    public function getThresholdMs(): float
    {
        return $this->thresholdMs;
    }

    /**
     * Sets a new threshold in milliseconds.
     */
    public function setThresholdMs(float $thresholdMs): void
    {
        $this->thresholdMs = $thresholdMs;
    }

    /**
     * Resets the slow query log and statistics.
     */
    public function reset(): void
    {
        $this->slowQueries = [];
        $this->fingerprintCounts = [];
    }
}
