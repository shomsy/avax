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
    ) {
    }

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
            default => 'SLOW',
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
            'sql' => $this->sql,
            'bindings' => $this->bindings,
            'duration_ms' => $this->durationMs,
            'threshold_ms' => $this->thresholdMs,
            'fingerprint' => $this->fingerprint,
            'connection' => $this->connection,
            'timestamp' => $this->timestamp,
            'occurrences' => $this->occurrences,
            'severity' => $this->severityLabel(),
            'times_over' => $this->timesOverThreshold(),
        ];
    }
}
