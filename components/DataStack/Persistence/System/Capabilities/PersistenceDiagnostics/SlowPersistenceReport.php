<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\PersistenceDiagnostics;

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
        public int $occurrences = 1,
    ) {
    }

    /**
     * Creates a slow persistence report.
     */
    public static function create(
        string $operation,
        string $type,
        float $durationMs,
        float $thresholdMs,
        array $context = [],
    ): self {
        return new self(
            operation: $operation,
            type: $type,
            durationMs: $durationMs,
            thresholdMs: $thresholdMs,
            fingerprint: self::computeFingerprint($operation, $type, $context),
            context: $context,
            timestamp: microtime(true),
        );
    }

    /**
     * Computes a fingerprint for the operation.
     */
    private static function computeFingerprint(string $operation, string $type, array $context): string
    {
        $normalized = $type.':'.$operation;

        if (isset($context['entity'])) {
            $normalized .= ':entity:'.$context['entity'];
        }

        if (isset($context['table'])) {
            $normalized .= ':table:'.$context['table'];
        }

        return hash('sha256', $normalized);
    }

    /**
     * Returns a human-readable summary.
     */
    public function summary(): string
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
            'operation' => $this->operation,
            'type' => $this->type,
            'duration_ms' => $this->durationMs,
            'threshold_ms' => $this->thresholdMs,
            'fingerprint' => $this->fingerprint,
            'context' => $this->context,
            'timestamp' => $this->timestamp,
            'occurrences' => $this->occurrences,
            'severity' => $this->severityLabel(),
            'times_over' => $this->timesOverThreshold(),
        ];
    }
}
