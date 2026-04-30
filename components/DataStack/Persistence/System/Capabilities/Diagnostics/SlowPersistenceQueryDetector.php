<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\Diagnostics;

use InvalidArgumentException;
use function count;

/**
 * A report for a detected slow persistence operation.
 *
 * Readonly value object containing information about a persistence
 * operation that exceeded the configured threshold.
 */
final readonly class SlowPersistenceReport
{
    public function __construct(
        public string $operation,
        public string $type,
        public float $durationMs,
        public float $thresholdMs,
        public string $fingerprint = '',
        public array $context = [],
        public float $timestamp = 0.0,
        public int   $occurrences = 1,
    ) {}

    /**
     * Creates a slow persistence report.
     */
    public static function create(
        string $operation,
        string $type,
        float $durationMs,
        float $thresholdMs,
        array $context = [],
    ) : self
    {
        return new self(
            operation  : $operation,
            type       : $type,
            durationMs : $durationMs,
            thresholdMs: $thresholdMs,
            fingerprint: self::computeFingerprint($operation, $type, $context),
            context    : $context,
            timestamp  : microtime(true),
        );
    }

    /**
     * Computes a fingerprint for the operation.
     */
    private static function computeFingerprint(string $operation, string $type, array $context) : string
    {
        $normalized = $type . ':' . $operation;

        if (isset($context['entity'])) {
            $normalized .= ':entity:' . $context['entity'];
        }

        if (isset($context['table'])) {
            $normalized .= ':table:' . $context['table'];
        }

        return hash('sha256', $normalized);
    }

    /**
     * Returns a human-readable summary.
     */
    public function summary() : string
    {
        return sprintf(
            "[%s] %s (%s) took %.2fms (threshold: %.2fms, %.1fx over)\nContext: %s",
            $this->severityLabel(),
            $this->type,
            $this->operation,
            $this->durationMs,
            $this->thresholdMs,
            $this->timesOverThreshold(),
            json_encode($this->context, JSON_THROW_ON_ERROR),
        );
    }

    /**
     * Returns a severity label.
     */
    public function severityLabel() : string
    {
        $ratio = $this->timesOverThreshold();

        return match (true) {
            $ratio >= 10.0 => 'CRITICAL',
            $ratio >= 5.0  => 'SEVERE',
            $ratio >= 2.0  => 'WARNING',
            default        => 'SLOW',
        };
    }

    /**
     * Returns how many times the threshold was exceeded.
     */
    public function timesOverThreshold() : float
    {
        if ($this->thresholdMs <= 0) {
            return 0.0;
        }

        return $this->durationMs / $this->thresholdMs;
    }

    /**
     * Converts to an associative array.
     */
    public function toArray() : array
    {
        return [
            'operation'    => $this->operation,
            'type'         => $this->type,
            'duration_ms'  => $this->durationMs,
            'threshold_ms' => $this->thresholdMs,
            'fingerprint'  => $this->fingerprint,
            'context'      => $this->context,
            'timestamp'    => $this->timestamp,
            'occurrences'  => $this->occurrences,
            'severity'     => $this->severityLabel(),
            'times_over'   => $this->timesOverThreshold(),
        ];
    }
}

/**
 * Statistics about slow persistence operations.
 */
final readonly class SlowPersistenceStatistics
{
    public function __construct(
        public int $totalSlowOperations = 0,
        public float $totalDurationMs = 0.0,
        public float $averageDurationMs = 0.0,
        public float $maxDurationMs = 0.0,
        public float $minDurationMs = 0.0,
        public array $countsByType = [],
        public array $countsByOperation = [],
        public array $countsByFingerprint = [],
    ) {}

    /**
     * Creates statistics from a list of slow persistence reports.
     *
     * @param list<SlowPersistenceReport> $reports
     */
    public static function fromReports(array $reports) : self
    {
        if (empty($reports)) {
            return new self();
        }

        $totalDuration = 0.0;
        $maxDuration   = 0.0;
        $minDuration   = PHP_FLOAT_MAX;
        $byType        = [];
        $byOperation   = [];
        $byFingerprint = [];

        foreach ($reports as $report) {
            $totalDuration += $report->durationMs;

            if ($report->durationMs > $maxDuration) {
                $maxDuration = $report->durationMs;
            }

            if ($report->durationMs < $minDuration) {
                $minDuration = $report->durationMs;
            }

            $byType[$report->type]           = ($byType[$report->type] ?? 0) + 1;
            $byOperation[$report->operation] = ($byOperation[$report->operation] ?? 0) + 1;
            $byFingerprint[$report->fingerprint] = ($byFingerprint[$report->fingerprint] ?? 0) + 1;
        }

        $count = count($reports);

        return new self(
            totalSlowOperations: $count,
            totalDurationMs    : $totalDuration,
            averageDurationMs  : $totalDuration / $count,
            maxDurationMs      : $maxDuration,
            minDurationMs      : $minDuration,
            countsByType       : $byType,
            countsByOperation  : $byOperation,
            countsByFingerprint: $byFingerprint,
        );
    }

    /**
     * Returns a summary string.
     */
    public function summary() : string
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

/**
 * Detects slow persistence operations across all query types.
 *
 * Unlike SlowQueryDetector (which focuses on SQL queries at the database level),
 * this detector operates at the persistence layer and tracks slow operations
 * across repositories, identity maps, unit of work, and other persistence
 * components.
 *
 * Operation types tracked:
 * - find: Entity/aggregate retrieval
 * - save: Entity persistence
 * - delete: Entity removal
 * - query: Complex query execution
 * - hydrate: Object hydration from data
 * - flush: Unit of work flush
 * - transaction: Transaction commit/rollback
 */
final class SlowPersistenceQueryDetector
{
    /**
     * @var float Threshold in milliseconds for what constitutes a "slow" operation
     */
    private float $thresholdMs;

    /**
     * @var list<SlowPersistenceReport> The slow operation log
     */
    private array $slowOperations = [];

    /**
     * @var array<string, int> Count of slow operations per fingerprint
     */
    private array $fingerprintCounts = [];

    /**
     * @var array<string, float> Running totals per type
     */
    private array $typeTotals = [];

    /**
     * @var int Maximum number of reports to retain (0 = unlimited)
     */
    private int $maxEntries;

    /**
     * @var int Total operations recorded (including fast ones)
     */
    private int $totalOperations = 0;

    /**
     * @var float Total time spent on all operations
     */
    private float $totalTimeMs = 0.0;

    public function __construct(
        float $thresholdMs = 100.0,
        int $maxEntries = 0,
    )
    {
        $this->thresholdMs = $thresholdMs;
        $this->maxEntries  = $maxEntries;
    }

    /**
     * Records a persistence operation using a callback that measures execution time.
     *
     * @template T
     * @param string               $operation Operation name
     * @param string               $type      Operation type
     * @param callable(): T        $callback  The operation to time
     * @param array<string, mixed> $context   Additional context
     *
     * @return T
     */
    public function time(string $operation, string $type, callable $callback, array $context = []) : mixed
    {
        $start = microtime(true);

        try {
            $result = $callback();
        } finally {
            $durationMs = (microtime(true) - $start) * 1000;
            $this->record($operation, $type, $durationMs, $context);
        }

        return $result;
    }

    /**
     * Records a persistence operation and checks if it's slow.
     *
     * @param string               $operation  Operation name (e.g., 'findById', 'save', 'delete')
     * @param string               $type       Operation type (e.g., 'find', 'save', 'delete', 'query')
     * @param float                $durationMs Duration in milliseconds
     * @param array<string, mixed> $context    Additional context (entity name, table, etc.)
     *
     * @return bool Whether the operation was classified as slow
     */
    public function record(
        string $operation,
        string $type,
        float $durationMs,
        array $context = [],
    ) : bool
    {
        $this->totalOperations++;
        $this->totalTimeMs += $durationMs;
        $this->typeTotals[$type] = ($this->typeTotals[$type] ?? 0.0) + $durationMs;

        if ($durationMs < $this->thresholdMs) {
            return false;
        }

        $report = SlowPersistenceReport::create(
            operation  : $operation,
            type       : $type,
            durationMs : $durationMs,
            thresholdMs: $this->thresholdMs,
            context    : $context,
        );

        $this->addReport($report);

        return true;
    }

    /**
     * Adds a slow persistence report directly.
     */
    public function addReport(SlowPersistenceReport $report) : void
    {
        $this->slowOperations[] = $report;

        $this->fingerprintCounts[$report->fingerprint]
            = ($this->fingerprintCounts[$report->fingerprint] ?? 0) + 1;

        // Enforce max entries
        if ($this->maxEntries > 0 && count($this->slowOperations) > $this->maxEntries) {
            array_shift($this->slowOperations);
        }
    }

    /**
     * Returns all recorded slow operations.
     *
     * @return list<SlowPersistenceReport>
     */
    public function all() : array
    {
        return $this->slowOperations;
    }

    /**
     * Returns slow operations filtered by type.
     *
     * @return list<SlowPersistenceReport>
     */
    public function byType(string $type) : array
    {
        return array_values(array_filter(
                                $this->slowOperations,
                                static fn (SlowPersistenceReport $r) : bool => $r->type === $type,
                            ));
    }

    /**
     * Returns slow operations filtered by operation name.
     *
     * @return list<SlowPersistenceReport>
     */
    public function byOperation(string $operation) : array
    {
        return array_values(array_filter(
                                $this->slowOperations,
                                static fn (SlowPersistenceReport $r) : bool => $r->operation === $operation,
                            ));
    }

    /**
     * Returns slow operations filtered by severity label.
     *
     * @return list<SlowPersistenceReport>
     */
    public function bySeverity(string $severity) : array
    {
        return array_values(array_filter(
                                $this->slowOperations,
                                static fn (SlowPersistenceReport $r) : bool => $r->severityLabel() === $severity,
                            ));
    }

    /**
     * Returns the N slowest operations.
     *
     * @return list<SlowPersistenceReport>
     */
    public function topSlow(int $limit = 10) : array
    {
        $sorted = $this->slowOperations;
        usort(
            $sorted,
            static fn (SlowPersistenceReport $a, SlowPersistenceReport $b) : int => $b->durationMs <=> $a->durationMs,
        );

        return array_slice($sorted, 0, $limit);
    }

    /**
     * Returns computed statistics about slow operations.
     */
    public function statistics() : SlowPersistenceStatistics
    {
        return SlowPersistenceStatistics::fromReports($this->slowOperations);
    }

    /**
     * Returns the count of slow operations per fingerprint.
     *
     * @return array<string, int>
     */
    public function fingerprintCounts() : array
    {
        return $this->fingerprintCounts;
    }

    /**
     * Returns the total count of slow operations.
     */
    public function count() : int
    {
        return count($this->slowOperations);
    }

    /**
     * Returns the total count of all recorded operations.
     */
    public function totalOperationCount() : int
    {
        return $this->totalOperations;
    }

    /**
     * Returns the current threshold in milliseconds.
     */
    public function getThresholdMs() : float
    {
        return $this->thresholdMs;
    }

    /**
     * Sets a new threshold in milliseconds.
     */
    public function setThresholdMs(float $thresholdMs) : void
    {
        if ($thresholdMs <= 0) {
            throw new InvalidArgumentException('Threshold must be greater than zero');
        }

        $this->thresholdMs = $thresholdMs;
    }

    /**
     * Returns the total time spent on operations of a specific type.
     */
    public function typeTotalMs(string $type) : float
    {
        return $this->typeTotals[$type] ?? 0.0;
    }

    /**
     * Returns all type totals.
     *
     * @return array<string, float>
     */
    public function typeTotals() : array
    {
        return $this->typeTotals;
    }

    /**
     * Returns a summary of the detector's state.
     */
    public function summary() : string
    {
        return sprintf(
            "Slow Persistence Detector:\n"
            . "  Threshold: %.2fms\n"
            . "  Total Operations: %d\n"
            . "  Slow Operations: %d (%.1f%%)\n"
            . "  Average Duration: %.2fms\n"
            . '  Total Time: %.2fms',
            $this->thresholdMs,
            $this->totalOperations,
            count($this->slowOperations),
            $this->slowPercentage(),
            $this->averageDurationMs(),
            $this->totalTimeMs,
        );
    }

    /**
     * Returns the percentage of operations that were slow.
     */
    public function slowPercentage() : float
    {
        if ($this->totalOperations === 0) {
            return 0.0;
        }

        return (count($this->slowOperations) / $this->totalOperations) * 100;
    }

    /**
     * Returns the average duration of all operations (including fast ones).
     */
    public function averageDurationMs() : float
    {
        if ($this->totalOperations === 0) {
            return 0.0;
        }

        return $this->totalTimeMs / $this->totalOperations;
    }

    /**
     * Resets the detector, clearing all recorded data.
     */
    public function reset() : void
    {
        $this->slowOperations    = [];
        $this->fingerprintCounts = [];
        $this->typeTotals        = [];
        $this->totalOperations   = 0;
        $this->totalTimeMs       = 0.0;
    }
}
