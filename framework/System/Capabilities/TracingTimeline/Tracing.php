<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\TracingTimeline;

/**
 * Main tracing manager for request execution timeline.
 */
final class Tracing
{
    private RuntimeTimeline|null $runtimeTimeline = null;

    public function start(): RuntimeTimeline
    {
        $this->runtimeTimeline = new RuntimeTimeline();

        $this->runtimeTimeline->record(name: 'request.received');

        return $this->runtimeTimeline;
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function record(
        string $name, float|null $durationMS = null, string|null $category = null,
        array $metadata = [],
    ): void {
        $this->runtimeTimeline?->record(
            name      : $name,
            durationMS: $durationMS,
            category  : $category,
            metadata  : $metadata,
        );
    }

    public function timeline() : RuntimeTimeline|null
    {
        return $this->runtimeTimeline;
    }

    public function begin(string $name, string|null $category = null) : TraceSpan
    {
        return $this->runtimeTimeline?->begin(
            name    : $name,
            category: $category,
        ) ?? new TraceSpan(name: $name);
    }

    public function finish(): void
    {
        $this->runtimeTimeline?->finish();
    }

    public function export(): string
    {
        return $this->runtimeTimeline?->exportText() ?? 'No active timeline';
    }
}
