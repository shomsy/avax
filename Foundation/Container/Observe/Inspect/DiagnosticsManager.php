<?php

declare(strict_types=1);
namespace Avax\Container\Observe\Inspect;

use Avax\Container\Observe\Metrics\MetricsCollector;
use Avax\Container\Observe\Timeline\ResolutionTimeline;

/**
 * Facade exposing inspection and telemetry services.
 *
 * This manager gives you one place to access container diagnostics: inspection, metrics, and timeline.
 * Telemetry tools are optional; when disabled, they are exposed as `null`.
 *
 * @see docs/Observe/Inspect/DiagnosticsManager.md#quick-summary
 */
readonly class DiagnosticsManager
{
    /**
     * @param Inspector               $inspector Diagnostics inspector instance.
     * @param MetricsCollector|null   $metrics   Collector to store timing metrics.
     * @param ResolutionTimeline|null $timeline  Timeline tracker for resolution operations.
     * @see docs/Observe/Inspect/DiagnosticsManager.md#method-__construct
     */
    public function __construct(
        private Inspector               $inspector,
        private MetricsCollector|null   $metrics = null,
        private ResolutionTimeline|null $timeline = null
    ) {}

    /**
     * Either return the base inspector or inspect a single service.
     *
     * @param string|null $id Service identifier to inspect; when null returns the inspector itself
     *
     * @return array|Inspector Inspection result array or the inspector instance
     * @see docs/Observe/Inspect/DiagnosticsManager.md#method-inspect
     */
    public function inspect(string|null $id = null) : array|Inspector
    {
        if ($id === null) {
            return $this->inspector;
        }

        return $this->inspector->inspect(id: $id);
    }

    /**
     * Retrieve the metrics collector when telemetry is enabled.
     *
     * @return MetricsCollector|null
     * @see docs/Observe/Inspect/DiagnosticsManager.md#method-metrics
     */
    public function metrics() : MetricsCollector|null
    {
        return $this->metrics;
    }

    /**
     * Get the timeline used for resolution diagnostics.
     *
     * @return ResolutionTimeline|null
     * @see docs/Observe/Inspect/DiagnosticsManager.md#method-timeline
     */
    public function timeline() : ResolutionTimeline|null
    {
        return $this->timeline;
    }
}
