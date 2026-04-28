<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\DI\Capabilities\Diagnostics\Observability;

/**
 * Bundles low-cost metrics and optional timeline diagnostics.
 */
final readonly class ResolutionTelemetry
{
    private ResolutionMetrics $metrics;

    private ResolutionTimeline $timeline;

    public function __construct(
        ResolutionMetrics|null  $metrics = null,
        ResolutionTimeline|null $timeline = null
    )
    {
        $this->metrics  = $metrics ?? new ResolutionMetrics;
        $this->timeline = $timeline ?? new ResolutionTimeline;
    }

    /**
     * Returns the metrics sink for container events.
     */
    public function metrics() : ResolutionMetrics
    {
        return $this->metrics;
    }

    /**
     * Returns the timeline sink for detailed resolution traces.
     */
    public function timeline() : ResolutionTimeline
    {
        return $this->timeline;
    }

    /**
     * Exports metrics in a machine-readable format.
     */
    public function exportMetrics() : string
    {
        return $this->metrics->export();
    }

    /**
     * Clears all collected observability state.
     */
    public function reset() : void
    {
        $this->metrics->reset();
        $this->timeline->reset();
    }
}
