<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Observability\System\PublicSurface;

use Avax\Components\Operations\Observability\System\Capabilities\Audit\AuditEvent;
use Avax\Components\Operations\Observability\System\Capabilities\Logs\StructuredLogRecord;
use Avax\Components\Operations\Observability\System\Capabilities\Metrics\Counter;
use Avax\Components\Operations\Observability\System\Capabilities\Metrics\Gauge;
use Avax\Components\Operations\Observability\System\Capabilities\Metrics\Histogram;
use Avax\Components\Operations\Observability\System\Capabilities\Tracing\Span;
use Avax\Components\Operations\Observability\System\PublicSurface\Observability;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ObservabilityTest extends TestCase
{
    #[Test]
    public function it_creates_span() : void
    {
        $span = Observability::trace('http.request', 'GET /api/users');

        self::assertInstanceOf(Span::class, $span);
        self::assertSame('http.request', $span->name);
        self::assertSame('GET /api/users', $span->operation);
    }

    #[Test]
    public function it_creates_counter() : void
    {
        $counter = Observability::counter('requests_total');

        self::assertInstanceOf(Counter::class, $counter);
        self::assertSame('requests_total', $counter->name);
        self::assertSame(0.0, $counter->getValue());
    }

    #[Test]
    public function it_creates_gauge() : void
    {
        $gauge = Observability::gauge('cpu_usage');

        self::assertInstanceOf(Gauge::class, $gauge);
        self::assertSame('cpu_usage', $gauge->name);
        self::assertSame(0.0, $gauge->getValue());
    }

    #[Test]
    public function it_creates_histogram() : void
    {
        $histogram = Observability::histogram('response_time');

        self::assertInstanceOf(Histogram::class, $histogram);
        self::assertSame('response_time', $histogram->name);
        self::assertSame(0, $histogram->count());
    }

    #[Test]
    public function it_creates_structured_log_record() : void
    {
        $record = Observability::log('info', 'User authenticated', ['user_id' => 1]);

        self::assertInstanceOf(StructuredLogRecord::class, $record);
        self::assertSame('info', $record->level);
        self::assertSame('User authenticated', $record->message);
        self::assertSame(['user_id' => 1], $record->context);
    }

    #[Test]
    public function it_creates_log_record_without_context() : void
    {
        $record = Observability::log('debug', 'Simple message');

        self::assertSame('debug', $record->level);
        self::assertSame('Simple message', $record->message);
        self::assertSame([], $record->context);
    }

    #[Test]
    public function it_creates_audit_event() : void
    {
        $event = Observability::audit('admin', 'user.created', 'user:123', ['role' => 'editor']);

        self::assertInstanceOf(AuditEvent::class, $event);
        self::assertSame('admin', $event->actor);
        self::assertSame('user.created', $event->action);
        self::assertSame('user:123', $event->target);
        self::assertSame(['role' => 'editor'], $event->metadata);
    }

    #[Test]
    public function it_creates_audit_event_without_metadata() : void
    {
        $event = Observability::audit('system', 'heartbeat', 'monitor');

        self::assertSame('system', $event->actor);
        self::assertSame('heartbeat', $event->action);
        self::assertSame('monitor', $event->target);
        self::assertSame([], $event->metadata);
    }

    #[Test]
    public function it_creates_multiple_independent_spans() : void
    {
        $span1 = Observability::trace('db.query', 'SELECT * FROM users');
        $span2 = Observability::trace('cache.get', 'user:1');

        self::assertNotSame($span1, $span2);
        self::assertSame('db.query', $span1->name);
        self::assertSame('cache.get', $span2->name);
    }

    #[Test]
    public function it_creates_multiple_independent_counters() : void
    {
        $counter1 = Observability::counter('requests');
        $counter2 = Observability::counter('errors');

        $counter1->increment();
        $counter2->increment(5.0);

        self::assertSame(1.0, $counter1->getValue());
        self::assertSame(5.0, $counter2->getValue());
    }

    #[Test]
    public function it_creates_multiple_independent_gauges() : void
    {
        $gauge1 = Observability::gauge('cpu');
        $gauge2 = Observability::gauge('memory');

        $gauge1->set(75.0);
        $gauge2->set(80.0);

        self::assertSame(75.0, $gauge1->getValue());
        self::assertSame(80.0, $gauge2->getValue());
    }

    #[Test]
    public function it_creates_multiple_independent_histograms() : void
    {
        $hist1 = Observability::histogram('latency_api');
        $hist2 = Observability::histogram('latency_db');

        $hist1->record(100.0);
        $hist2->record(50.0);

        self::assertSame(1, $hist1->count());
        self::assertSame(1, $hist2->count());
    }

    #[Test]
    public function it_is_readonly() : void
    {
        $reflection = new ReflectionClass(Observability::class);

        self::assertTrue($reflection->isReadonly());
    }

    #[Test]
    public function it_is_final() : void
    {
        $reflection = new ReflectionClass(Observability::class);

        self::assertTrue($reflection->isFinal());
    }

    #[Test]
    public function it_provides_unified_observability_surface() : void
    {
        $span      = Observability::trace('request', 'POST /api/data');
        $counter   = Observability::counter('api_calls');
        $gauge     = Observability::gauge('active_connections');
        $histogram = Observability::histogram('response_times');
        $log       = Observability::log('info', 'Request processed');
        $audit     = Observability::audit('user', 'data.created', 'resource:1');

        $counter->increment();
        $gauge->set(10.0);
        $histogram->record(150.0);
        $span->end();

        self::assertInstanceOf(Span::class, $span);
        self::assertInstanceOf(Counter::class, $counter);
        self::assertInstanceOf(Gauge::class, $gauge);
        self::assertInstanceOf(Histogram::class, $histogram);
        self::assertInstanceOf(StructuredLogRecord::class, $log);
        self::assertInstanceOf(AuditEvent::class, $audit);
    }

    #[Test]
    public function it_handles_all_log_levels() : void
    {
        $levels = ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'];

        foreach ($levels as $level) {
            $record = Observability::log($level, "Test {$level} message");
            self::assertSame($level, $record->level);
        }
    }
}
