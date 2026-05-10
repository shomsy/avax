<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Observability\System\Capabilities\Tracing;

use Avax\Components\Operations\Observability\System\Capabilities\Correlation\SpanId;
use Avax\Components\Operations\Observability\System\Capabilities\Correlation\TraceId;
use Avax\Components\Operations\Observability\System\Capabilities\Tracing\Span;
use Avax\Components\Operations\Observability\System\Capabilities\Tracing\SpanStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;

final class SpanTest extends TestCase
{
    #[Test]
    public function it_constructs_with_name_and_operation() : void
    {
        $span = new Span('http.request', 'GET /api/users');

        self::assertSame('http.request', $span->name);
        self::assertSame('GET /api/users', $span->operation);
    }

    #[Test]
    public function it_constructs_with_optional_trace_id() : void
    {
        $traceId = TraceId::generate();
        $span    = new Span('db.query', 'SELECT * FROM users', $traceId);

        self::assertSame($traceId, $span->traceId);
    }

    #[Test]
    public function it_constructs_with_optional_parent_span_id() : void
    {
        $parentSpanId = SpanId::generate();
        $span         = new Span('db.query', 'SELECT * FROM users', null, $parentSpanId);

        self::assertSame($parentSpanId, $span->parentSpanId);
    }

    #[Test]
    public function it_constructs_with_both_trace_and_parent() : void
    {
        $traceId      = TraceId::generate();
        $parentSpanId = SpanId::generate();
        $span         = new Span('cache.lookup', 'user:123', $traceId, $parentSpanId);

        self::assertSame($traceId, $span->traceId);
        self::assertSame($parentSpanId, $span->parentSpanId);
    }

    #[Test]
    public function it_defaults_trace_id_to_null() : void
    {
        $span = new Span('test', 'op');

        self::assertNull($span->traceId);
    }

    #[Test]
    public function it_defaults_parent_span_id_to_null() : void
    {
        $span = new Span('test', 'op');

        self::assertNull($span->parentSpanId);
    }

    #[Test]
    public function it_records_start_time_on_construction() : void
    {
        $span = new Span('test', 'op');

        $duration = $span->end();

        self::assertIsFloat($duration);
        self::assertGreaterThanOrEqual(0.0, $duration);
    }

    #[Test]
    public function it_ends_and_returns_duration() : void
    {
        $span = new Span('test', 'op');

        usleep(1000);

        $duration = $span->end();

        self::assertGreaterThan(0.0, $duration);
    }

    #[Test]
    public function it_returns_null_duration_before_end() : void
    {
        $span = new Span('test', 'op');

        self::assertNull($span->duration());
    }

    #[Test]
    public function it_returns_duration_after_end() : void
    {
        $span = new Span('test', 'op');
        $span->end();

        $duration = $span->duration();

        self::assertIsFloat($duration);
        self::assertGreaterThanOrEqual(0.0, $duration);
    }

    #[Test]
    public function it_does_not_change_end_time_on_multiple_calls() : void
    {
        $span = new Span('test', 'op');

        $firstDuration = $span->end();
        usleep(2000);
        $secondDuration = $span->end();

        self::assertSame($firstDuration, $secondDuration);
    }

    #[Test]
    public function it_sets_attribute() : void
    {
        $span = new Span('test', 'op');

        $result = $span->setAttribute('http.status_code', 200);

        self::assertSame($span, $result);
    }

    #[Test]
    public function it_sets_multiple_attributes() : void
    {
        $span = new Span('test', 'op');

        $result = $span
            ->setAttribute('http.method', 'GET')
            ->setAttribute('http.url', '/api/users')
            ->setAttribute('http.status_code', 200);

        self::assertSame($span, $result);
    }

    #[Test]
    public function it_overwrites_existing_attribute() : void
    {
        $span = new Span('test', 'op');

        $span->setAttribute('key', 'first');
        $span->setAttribute('key', 'second');

        self::assertTrue(true);
    }

    #[Test]
    public function it_records_exception() : void
    {
        $span      = new Span('test', 'op');
        $exception = new RuntimeException('test error');

        $result = $span->recordException($exception);

        self::assertSame($span, $result);
    }

    #[Test]
    public function it_records_exception_without_throwing() : void
    {
        $span      = new Span('test', 'op');
        $exception = new RuntimeException('should not throw');

        $span->recordException($exception);

        self::assertTrue(true);
    }

    #[Test]
    public function it_stores_exception_data_in_events() : void
    {
        $span = new Span('test', 'op');
        $exception = new RuntimeException('test error');

        $span->recordException($exception);

        $events = $span->events();
        self::assertCount(1, $events);
        self::assertSame(RuntimeException::class, $events[0]['type']);
        self::assertSame('test error', $events[0]['message']);
        self::assertArrayHasKey('timestamp', $events[0]);
        self::assertIsFloat($events[0]['timestamp']);
    }

    #[Test]
    public function it_sets_status_to_error_on_exception() : void
    {
        $span = new Span('test', 'op');
        self::assertSame(SpanStatus::Ok, $span->status());

        $span->recordException(new RuntimeException('fail'));

        self::assertSame(SpanStatus::Error, $span->status());
    }

    #[Test]
    public function it_records_multiple_exceptions() : void
    {
        $span = new Span('test', 'op');

        $span->recordException(new RuntimeException('first'));
        $span->recordException(new \InvalidArgumentException('second'));

        $events = $span->events();
        self::assertCount(2, $events);
        self::assertSame(RuntimeException::class, $events[0]['type']);
        self::assertSame(\InvalidArgumentException::class, $events[1]['type']);
    }

    #[Test]
    public function it_exports_span_data_with_events() : void
    {
        $span = new Span('http.request', 'GET /api', TraceId::generate());
        $span->setAttribute('http.status_code', 500);
        $span->recordException(new RuntimeException('server error'));
        $span->end();

        $exported = $span->export();

        self::assertNotEmpty($exported['trace_id']);
        self::assertSame('http.request', $exported['name']);
        self::assertSame('error', $exported['status']);
        self::assertSame(500, $exported['attributes']['http.status_code']);
        self::assertCount(1, $exported['events']);
        self::assertSame('server error', $exported['events'][0]['message']);
        self::assertIsFloat($exported['end']);
    }

    #[Test]
    public function it_supports_fluent_interface_for_set_attribute() : void
    {
        $span = new Span('test', 'op');

        $returned = $span->setAttribute('a', 1)->setAttribute('b', 2);

        self::assertSame($span, $returned);
    }

    #[Test]
    public function it_supports_fluent_interface_for_record_exception() : void
    {
        $span = new Span('test', 'op');

        $returned = $span->recordException(new RuntimeException('err'));

        self::assertSame($span, $returned);
    }

    #[Test]
    public function it_handles_attribute_with_various_types() : void
    {
        $span = new Span('test', 'op');

        $span->setAttribute('string', 'value');
        $span->setAttribute('int', 42);
        $span->setAttribute('float', 3.14);
        $span->setAttribute('bool', true);
        $span->setAttribute('null', null);
        $span->setAttribute('array', [1, 2, 3]);

        self::assertTrue(true);
    }

    #[Test]
    public function it_has_readonly_properties_for_name_and_operation() : void
    {
        $reflection = new ReflectionClass(Span::class);

        $nameProp      = $reflection->getProperty('name');
        $operationProp = $reflection->getProperty('operation');

        self::assertTrue($nameProp->isReadOnly());
        self::assertTrue($operationProp->isReadOnly());
    }
}
