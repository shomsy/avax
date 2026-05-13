<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Operations\Observability;

use Avax\Components\Operations\Observability\System\Capabilities\Correlation\CorrelationId;
use Avax\Components\Operations\Observability\System\Capabilities\MetricsCollector\MetricsCollector;
use Avax\Components\Operations\Observability\System\Capabilities\Tracing\TraceTimeline;
use Avax\Components\Operations\Observability\System\Flows\RecordObservability\RecordObservability;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ObservabilityTelemetryTest extends TestCase
{
    private MetricsCollector    $metrics;
    private TraceTimeline       $timeline;
    private RecordObservability $obs;

    protected function setUp() : void
    {
        $this->metrics  = new MetricsCollector();
        $this->timeline = new TraceTimeline();
        $this->obs      = new RecordObservability($this->metrics, $this->timeline);
    }

    // -- MetricsCollector tests

    public function test_counter_increment() : void
    {
        $collector = new MetricsCollector();
        $collector->incrementCounter('requests');
        $collector->incrementCounter('requests');

        self::assertSame(2.0, $collector->getCounter('requests'));
    }

    public function test_counter_with_amount() : void
    {
        $collector = new MetricsCollector();
        $collector->incrementCounter('bytes', 1024.0);

        self::assertSame(1024.0, $collector->getCounter('bytes'));
    }

    public function test_gauge_set_and_get() : void
    {
        $collector = new MetricsCollector();
        $collector->setGauge('memory', 256.0);

        self::assertSame(256.0, $collector->getGauge('memory'));
    }

    public function test_gauge_overwrites() : void
    {
        $collector = new MetricsCollector();
        $collector->setGauge('temperature', 20.0);
        $collector->setGauge('temperature', 25.0);

        self::assertSame(25.0, $collector->getGauge('temperature'));
    }

    public function test_histogram_observation() : void
    {
        $collector = new MetricsCollector();
        $collector->observeHistogram('latency', 100.0);
        $collector->observeHistogram('latency', 200.0);
        $collector->observeHistogram('latency', 150.0);

        $stats = $collector->getHistogramStats('latency');

        self::assertSame(3, $stats['count']);
        self::assertSame(450.0, $stats['sum']);
        self::assertSame(100.0, $stats['min']);
        self::assertSame(200.0, $stats['max']);
    }

    public function test_histogram_empty() : void
    {
        $collector = new MetricsCollector();

        $stats = $collector->getHistogramStats('nonexistent');

        self::assertSame(0, $stats['count']);
        self::assertNull($stats['min']);
    }

    public function test_metrics_snapshot() : void
    {
        $collector = new MetricsCollector();
        $collector->incrementCounter('a');
        $collector->setGauge('b', 42.0);
        $collector->observeHistogram('c', 10.0);

        $snapshot = $collector->snapshot();

        self::assertSame(1.0, $snapshot['counters']['a']);
        self::assertSame(42.0, $snapshot['gauges']['b']);
        self::assertSame(1, $snapshot['histograms']['c']['count']);
    }

    public function test_metrics_clear() : void
    {
        $collector = new MetricsCollector();
        $collector->incrementCounter('a');
        $collector->clear();

        self::assertSame(0.0, $collector->getCounter('a'));
    }

    // -- RecordObservability tests

    public function test_record_success() : void
    {
        $result = $this->obs->record(
            operation: 'getUser',
            callback: static fn () => ['id' => 1, 'name' => 'Alice'],
        );

        self::assertEquals(['id' => 1, 'name' => 'Alice'], $result);
    }

    public function test_record_increments_counter() : void
    {
        $this->obs->record(
            operation: 'testOp',
            callback: static fn () => null,
        );

        self::assertSame(1.0, $this->obs->getMetrics()->getCounter('operations.total'));
        self::assertSame(1.0, $this->obs->getMetrics()->getCounter('operations.testOp'));
    }

    public function test_record_error_increments_error_counter() : void
    {
        try {
            $this->obs->record(
                operation: 'failingOp',
                callback: static fn () => throw new RuntimeException('boom'),
            );
        } catch (RuntimeException) {
            // Expected
        }

        self::assertSame(1.0, $this->obs->getMetrics()->getCounter('operations.errors'));
    }

    public function test_record_with_correlation() : void
    {
        $corr = CorrelationId::fromString('test-corr-123');

        $this->obs->record(
            operation: 'correlated',
            callback: static fn () => 'done',
            correlationId: $corr,
        );

        // Verify span was added to timeline
        self::assertCount(1, $this->obs->getTimeline()->toArray());
    }

    public function test_log_with_redaction() : void
    {
        $log = $this->obs->log(
            level: 'info',
            message: 'User login',
            context: ['userId' => '123', 'password' => 'secret123'],
        );

        self::assertSame('info', $log['level']);
        self::assertSame('secret123' !== $log['context']['password'], true);
        self::assertSame('***', $log['context']['password']);
        self::assertSame('123', $log['context']['userId']);
    }

    public function test_log_increments_log_counter() : void
    {
        $this->obs->log(level: 'error', message: 'Something went wrong');

        self::assertSame(1.0, $this->obs->getMetrics()->getCounter('logs.error'));
    }

    // -- Full composition test

    public function test_full_observability_composition() : void
    {
        $corr = CorrelationId::generate();

        $result = $this->obs->record(
            operation: 'processOrder',
            callback: static fn () => 'order-123',
            correlationId: $corr,
        );

        self::assertEquals('order-123', $result);

        // Metrics recorded
        self::assertSame(1.0, $this->obs->getMetrics()->getCounter('operations.total'));
        self::assertSame(1.0, $this->obs->getMetrics()->getCounter('operations.processOrder'));
        self::assertSame(0.0, $this->obs->getMetrics()->getCounter('operations.errors'));

        // Tracing recorded
        $timeline = $this->obs->getTimeline()->toArray();
        self::assertCount(1, $timeline);
        self::assertSame('processOrder', $timeline[0]['name']);

        // Log with redaction
        $log = $this->obs->log(
            level: 'info',
            message: 'Order processed',
            context: ['orderId' => 'order-123', 'token' => 'abc123'],
        );

        self::assertSame('***', $log['context']['token']);
        self::assertSame(1.0, $this->obs->getMetrics()->getCounter('logs.info'));
    }
}
