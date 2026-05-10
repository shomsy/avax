<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Flows\RecordObservability;

use Avax\Components\Operations\Observability\System\Capabilities\Correlation\CorrelationId;
use Avax\Components\Operations\Observability\System\Capabilities\Correlation\SpanId;
use Avax\Components\Operations\Observability\System\Capabilities\Correlation\TraceId;
use Avax\Components\Operations\Observability\System\Capabilities\MetricsCollector\MetricsCollector;
use Avax\Components\Operations\Observability\System\Capabilities\Redaction\RedactSensitiveData;
use Avax\Components\Operations\Observability\System\Capabilities\Tracing\Span;
use Avax\Components\Operations\Observability\System\Capabilities\Tracing\TraceTimeline;
use Closure;

/**
 * Unified observability recorder composing correlation, tracing, metrics, and redaction.
 */
final class RecordObservability
{
    private MetricsCollector $metrics;
    private TraceTimeline $timeline;
    private RedactSensitiveData $redactor;

    public function __construct(
        ?MetricsCollector $metrics = null,
        ?TraceTimeline $timeline = null,
        ?RedactSensitiveData $redactor = null,
    ) {
        $this->metrics = $metrics ?? new MetricsCollector();
        $this->timeline = $timeline ?? new TraceTimeline();
        $this->redactor = $redactor ?? new RedactSensitiveData();
    }

    /**
     * Record an operation with full observability: correlation, tracing, metrics, redaction.
     *
     * @template TResult
     * @param Closure(): TResult $callback
     * @return TResult
     */
    public function record(
        string $operation,
        Closure $callback,
        ?CorrelationId $correlationId = null,
    ) : mixed {
        $corr = $correlationId ?? CorrelationId::generate();
        $traceId = TraceId::generate();
        $spanId = SpanId::generate();

        $span = new Span(
            name: $operation,
            operation: $operation,
            traceId: $traceId,
        );

        $span->setAttribute('correlationId', (string) $corr);

        $start = hrtime(true);
        $this->metrics->incrementCounter(name: 'operations.total');
        $this->metrics->incrementCounter(name: 'operations.' . $operation);

        try {
            $result = $callback();

            $duration = $span->end();
            $this->metrics->observeHistogram(name: 'operations.duration', value: $duration * 1000);

            return $result;
        } catch (\Throwable $e) {
            $span->setAttribute('exception', $e::class);
            $span->setAttribute('exception.message', $e->getMessage());
            $span->end();

            $this->metrics->incrementCounter(name: 'operations.errors');

            throw $e;
        } finally {
            $this->timeline->add($span);
        }
    }

    /**
     * Record a log entry with redaction.
     *
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function log(string $level, string $message, array $context = []) : array
    {
        $redacted = $this->redactor->redact($context);

        $this->metrics->incrementCounter(name: 'logs.' . $level);

        return [
            'level' => $level,
            'message' => $message,
            'context' => $redacted,
            'timestamp' => time(),
        ];
    }

    public function getMetrics() : MetricsCollector
    {
        return $this->metrics;
    }

    public function getTimeline() : TraceTimeline
    {
        return $this->timeline;
    }
}
