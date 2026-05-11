<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\TracingTimeline;

/**
 * Tracks the execution timeline of a request.
 */
final class RuntimeTimeline
{
    private readonly float $startMS;

    /**
     * @var list<RuntimeEvent>
     */
    private array $events = [];

    private bool $isFinished = false;

    private float|null $endMS = null;

    public function __construct()
    {
        $this->startMS = microtime(true) * 1000;
    }

    /**
     * Start timing a named operation.
     */
    public function begin(string $name, string|null $category = null) : TraceSpan
    {
        return new TraceSpan(
            name    : $name,
            onFinish: function (float $durationMS) use ($name, $category): void {
                $this->record(
                    name      : $name,
                    durationMS: $durationMS,
                    category  : $category,
                );
            },
        );
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function record(
        string $name, float|null $durationMS = null, string|null $category = null,
        array $metadata = [],
    ): void {
        $timestamp = microtime(true) * 1000;

        $this->events[] = new RuntimeEvent(
            name       : $name,
            timestampMS: $timestamp - $this->startMS,
            durationMS : $durationMS,
            category   : $category,
            metadata   : $metadata,
        );
    }

    public function finish(): void
    {
        $this->endMS = microtime(true) * 1000;
        $this->isFinished = true;

        $this->record(
            name      : 'request.completed',
            durationMS: $this->durationMS(),
        );
    }

    public function durationMS(): float
    {
        $end = $this->endMS ?? (microtime(true) * 1000);

        return $end - $this->startMS;
    }

    /**
     * @return list<RuntimeEvent>
     */
    public function events(): array
    {
        return $this->events;
    }

    public function isFinished(): bool
    {
        return $this->isFinished;
    }

    /**
     * Export timeline as formatted string.
     */
    public function exportText(): string
    {
        $lines = ['Execution Timeline:'];

        foreach ($this->events as $event) {
            $line = sprintf(
                '  %-30s %8.2fms',
                $event->name,
                $event->timestampMS,
            );

            if ($event->durationMS !== null) {
                $line .= sprintf(' (+%.2fms)', $event->durationMS);
            }

            $lines[] = $line;
        }

        $lines[] = sprintf(
            "\nTotal: %.2fms",
            $this->durationMS(),
        );

        return implode("\n", $lines);
    }

    /**
     * Export timeline as structured array.
     */
    /**
     * @return array{duration_ms: float, events: list<array{name: string, timestamp_ms: float, duration_ms: float|null,
     *                            category: string|null, metadata: array<string, mixed>}>}
     */
    public function exportArray(): array
    {
        return [
            'duration_ms' => $this->durationMS(),
            'events' => array_map(
                static fn (RuntimeEvent $runtimeEvent): array => [
                    'name' => $runtimeEvent->name,
                    'timestamp_ms' => round($runtimeEvent->timestampMS, 2),
                    'duration_ms' => $runtimeEvent->durationMS !== null ? round($runtimeEvent->durationMS, 2) : null,
                    'category' => $runtimeEvent->category,
                    'metadata' => $runtimeEvent->metadata,
                ],
                $this->events,
            ),
        ];
    }
}
