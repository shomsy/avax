<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Capabilities\Tracing;

use Avax\Components\Operations\Observability\System\Capabilities\Correlation\SpanId;
use Avax\Components\Operations\Observability\System\Capabilities\Correlation\TraceId;
use Throwable;

class Span
{
    private readonly float $startTime;

    private float|null $endTime = null;

    /**
     * @var array<string, mixed>
     */
    private array $attributes = [];

    /**
     * @var list<array{type: string, message: string, timestamp: float, stack_trace?: string}>
     */
    private array $events = [];

    private SpanStatus $status = SpanStatus::Ok;

    public function __construct(
        public readonly string $name,
        public readonly string $operation,
        public readonly ?TraceId $traceId = null,
        public readonly ?SpanId $parentSpanId = null,
    ) {
        $this->startTime = hrtime(true) / 1e9;
    }

    public function setAttribute(string $key, mixed $value): self
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    public function recordException(Throwable $throwable): self
    {
        $this->status = SpanStatus::Error;
        $this->events[] = [
            'type' => $throwable::class,
            'message' => $throwable->getMessage(),
            'timestamp' => hrtime(true) / 1e9,
            'stack_trace' => $this->truncateStackTrace($throwable->getTraceAsString()),
        ];

        return $this;
    }

    /**
     * Truncate stack trace to avoid excessive output.
     */
    private function truncateStackTrace(string $trace, int $maxLength = 2000) : string
    {
        if (strlen($trace) <= $maxLength) {
            return $trace;
        }

        return substr($trace, 0, $maxLength) . "\n... [truncated]";
    }

    public function end(): float
    {
        if ($this->endTime === null) {
            $this->endTime = hrtime(true) / 1e9;
        }

        return $this->endTime - $this->startTime;
    }

    public function duration() : float|null
    {
        return $this->endTime ? $this->endTime - $this->startTime : null;
    }

    /**
     * @return list<array{type: string, message: string, timestamp: float, stack_trace?: string}>
     */
    public function events(): array
    {
        return $this->events;
    }

    public function status(): SpanStatus
    {
        return $this->status;
    }

    /**
     * Export span data for writing to trace exporter.
     *
     * @return array{trace_id: string, span_id: string, parent_id: string|null, name: string, start: float, end: float|null, status: string, attributes: array<string, mixed>, events: list<array<string, mixed>>}
     */
    public function export() : array
    {
        return [
            'trace_id' => $this->traceId->value ?? '',
            'span_id' => '',
            'parent_id' => $this->parentSpanId?->value,
            'name' => $this->name,
            'start' => $this->startTime,
            'end' => $this->endTime,
            'status' => $this->status->value,
            'attributes' => $this->attributes,
            'events' => $this->events,
        ];
    }
}
