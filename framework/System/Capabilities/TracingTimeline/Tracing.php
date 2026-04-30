<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\TracingTimeline;

/**
 * Main tracing manager for request execution timeline.
 */
final class Tracing
{
    private RuntimeTimeline|null $currentTimeline = null;

    public function start() : RuntimeTimeline
    {
        $this->currentTimeline = new RuntimeTimeline();

        $this->currentTimeline->record(name: 'request.received');

        return $this->currentTimeline;
    }

    public function record(
        string      $name,
        float|null  $durationMS = null,
        string|null $category = null,
        array       $metadata = [],
    ) : void
    {
        $this->currentTimeline?->record(
            name      : $name,
            durationMS: $durationMS,
            category  : $category,
            metadata  : $metadata,
        );
    }

    public function timeline() : RuntimeTimeline|null
    {
        return $this->currentTimeline;
    }

    public function begin(string $name, string|null $category = null) : TraceSpan
    {
        return $this->currentTimeline?->begin(
            name    : $name,
            category: $category,
        ) ?? new TraceSpan(name: $name, category: $category);
    }

    public function finish() : void
    {
        $this->currentTimeline?->finish();
    }

    public function export() : string
    {
        return $this->currentTimeline?->exportText() ?? 'No active timeline';
    }
}
