<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Diagnostics\Observability;

/**
 * Bundles low-cost metrics and optional timeline diagnostics.
 */
final readonly class ResolutionTelemetry
{
    public function __construct(private ResolutionMetrics $resolutionMetrics = new ResolutionMetrics(), private ResolutionTimeline $resolutionTimeline = new ResolutionTimeline())
    {
    }

    /**
     * Returns the metrics sink for container events.
     */
    public function metrics() : ResolutionMetrics
    {
        return $this->resolutionMetrics;
    }

    /**
     * Returns the timeline sink for detailed resolution traces.
     */
    public function timeline() : ResolutionTimeline
    {
        return $this->resolutionTimeline;
    }

    /**
     * Exports metrics in a machine-readable format.
     */
    public function exportMetrics() : string
    {
        return $this->resolutionMetrics->export();
    }

    /**
     * Clears all collected observability state.
     */
    public function reset() : void
    {
        $this->resolutionMetrics->reset();
        $this->resolutionTimeline->reset();
    }
}
