<?php

declare(strict_types=1);

namespace Avax\Container\Observe\Metrics;

use Avax\Container\Observe\Metrics\Sink\TelemetrySinkInterface;

/**
 * Lightweight in-memory collector for basic resolution telemetry.
 *
 * @see docs/Observe/Metrics/MetricsCollector.md#quick-summary
 */
class MetricsCollector implements TelemetrySinkInterface
{
    private int $resolvedCount = 0;

    private float $totalResolutionTime = 0.0;

    private array $resolutionCounts = [];

    /**
     * {@inheritDoc}
     *
     * @see docs/Observe/Metrics/MetricsCollector.md#method-record
     */
    public function record(string $abstract, float $durationMs, string $strategy) : void
    {
        $this->resolvedCount++;
        $this->totalResolutionTime += $durationMs;

        if (! isset($this->resolutionCounts[$abstract])) {
            $this->resolutionCounts[$abstract] = 0;
        }

        $this->resolutionCounts[$abstract]++;
    }

    /**
     * Build a snapshot suitable for dashboards or API consumption.
     *
     * @return array Snapshot payload
     *
     * @see docs/Observe/Metrics/MetricsCollector.md#method-getsnapshot
     */
    public function getSnapshot() : array
    {
        return [
            'total_resolved'    => $this->resolvedCount,
            'total_time_ms'     => $this->totalResolutionTime,
            'avg_resolution_ms' => $this->resolvedCount > 0 ? $this->totalResolutionTime / $this->resolvedCount : 0,
            'counts'            => $this->resolutionCounts,
        ];
    }
}
