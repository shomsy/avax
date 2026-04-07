<?php

declare(strict_types=1);

namespace Avax\Container\Observability;

final readonly class ResolutionTelemetry
{
    private ResolutionMetrics $metrics;

    private ResolutionTimeline $timeline;

    public function __construct(
        ResolutionMetrics|null $metrics = null,
        ResolutionTimeline|null $timeline = null
    ) {
        $this->metrics = $metrics ?? new ResolutionMetrics;
        $this->timeline = $timeline ?? new ResolutionTimeline;
    }

    public function metrics() : ResolutionMetrics
    {
        return $this->metrics;
    }

    public function timeline() : ResolutionTimeline
    {
        return $this->timeline;
    }

    public function exportMetrics() : string
    {
        return $this->metrics->export();
    }

    public function reset() : void
    {
        $this->metrics->reset();
        $this->timeline->reset();
    }
}
