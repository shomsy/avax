<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Observability;

use Avax\Components\Operations\Observability\System\Capabilities\Correlation\CorrelationId;
use Avax\Components\Operations\Observability\System\Capabilities\Correlation\RequestId;
use Avax\Components\Operations\Observability\System\Capabilities\Correlation\SpanId;
use Avax\Components\Operations\Observability\System\Capabilities\Correlation\TraceId;
use Avax\Components\Operations\Observability\System\Capabilities\Metrics\Counter;
use Avax\Components\Operations\Observability\System\Capabilities\Metrics\Gauge;
use Avax\Components\Operations\Observability\System\Capabilities\Tracing\Span;
use PHPUnit\Framework\TestCase;

final class ObservabilityCapabilitiesTest extends TestCase
{
    // --- CorrelationId ---

    public function test_it_generates_unique_correlation_ids() : void
    {
        $id1 = CorrelationId::generate();
        $id2 = CorrelationId::generate();

        $this->assertNotSame($id1->value, $id2->value);
        $this->assertNotEmpty($id1->value);
    }

    public function test_it_creates_correlation_id_from_string() : void
    {
        $id = CorrelationId::fromString('my-trace-123');
        $this->assertSame('my-trace-123', $id->value);
        $this->assertSame('my-trace-123', (string) $id);
    }

    // --- Counter ---

    public function test_it_starts_counter_at_zero() : void
    {
        $counter = new Counter('http.requests');
        $this->assertSame(0.0, $counter->getValue());
    }

    public function test_it_increments_counter_by_default_amount() : void
    {
        $counter = new Counter('http.requests');
        $counter->increment();
        $this->assertSame(1.0, $counter->getValue());
    }

    public function test_it_increments_counter_by_custom_amount() : void
    {
        $counter = new Counter('http.requests');
        $counter->increment(5.0);
        $this->assertSame(5.0, $counter->getValue());
    }

    public function test_it_accumulates_counter_increments() : void
    {
        $counter = new Counter('http.requests');
        $counter->increment(3.0);
        $counter->increment(2.0);
        $this->assertSame(5.0, $counter->getValue());
    }

    public function test_it_preserves_counter_name_and_tags() : void
    {
        $counter = new Counter('api.calls', ['method' => 'GET']);
        $this->assertSame('api.calls', $counter->name);
        $this->assertSame(['method' => 'GET'], $counter->tags);
    }

    // --- Gauge ---

    public function test_it_starts_gauge_at_zero() : void
    {
        $gauge = new Gauge('memory.usage');
        $this->assertSame(0.0, $gauge->getValue());
    }

    public function test_it_sets_gauge_to_arbitrary_value() : void
    {
        $gauge = new Gauge('memory.usage');
        $gauge->set(128.5);
        $this->assertSame(128.5, $gauge->getValue());
    }

    public function test_it_replaces_gauge_value_on_set() : void
    {
        $gauge = new Gauge('cpu.usage');
        $gauge->set(75.0);
        $gauge->set(30.0);
        $this->assertSame(30.0, $gauge->getValue());
    }

    // --- Span ---

    public function test_it_creates_span_with_name_and_operation() : void
    {
        $span = new Span('db.query', 'SELECT');
        $this->assertSame('db.query', $span->name);
        $this->assertSame('SELECT', $span->operation);
    }

    public function test_it_returns_null_duration_before_end() : void
    {
        $span = new Span('http.request', 'GET');
        $this->assertNull($span->duration());
    }

    public function test_it_returns_positive_duration_after_end() : void
    {
        $span     = new Span('http.request', 'GET');
        $duration = $span->end();
        $this->assertGreaterThanOrEqual(0.0, $duration);
        $this->assertNotNull($span->duration());
    }

    public function test_it_does_not_change_duration_on_repeated_end() : void
    {
        $span  = new Span('http.request', 'GET');
        $first = $span->end();
        usleep(1000);
        $second = $span->end();
        $this->assertSame($first, $second);
    }

    public function test_it_accepts_trace_and_span_ids() : void
    {
        $traceId = TraceId::generate();
        $spanId  = SpanId::generate();
        $span    = new Span('child.op', 'PROCESS', $traceId, $spanId);
        $this->assertSame($traceId, $span->traceId);
        $this->assertSame($spanId, $span->parentSpanId);
    }

    public function test_it_sets_span_attributes() : void
    {
        $span   = new Span('test', 'op');
        $result = $span->setAttribute('key', 'value');
        $this->assertSame($span, $result);
    }
}
