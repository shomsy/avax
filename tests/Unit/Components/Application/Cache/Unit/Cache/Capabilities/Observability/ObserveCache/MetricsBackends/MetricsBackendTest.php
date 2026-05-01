<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Cache\Unit\Cache\Capabilities\Observability\ObserveCache\MetricsBackends;

use Avax\Components\Application\Cache\System\Capabilities\Observability\ObserveCache\CacheMetrics;
use Avax\Components\Application\Cache\System\Capabilities\Observability\ObserveCache\MetricsBackends\MetricsSink;
use Avax\Components\Application\Cache\System\Capabilities\Observability\ObserveCache\MetricsBackends\PrometheusBackend;
use Avax\Components\Application\Cache\System\Capabilities\Observability\ObserveCache\MetricsBackends\StatsDBackend;
use Avax\Components\Application\Cache\System\Foundation\Time\FrozenClock;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;
use PHPUnit\Framework\TestCase;

final class MetricsBackendTest extends TestCase
{
    public function test_prometheus_backend_counts() : void
    {
        $backend = new PrometheusBackend();

        $backend->increment(metric: 'cache.hit', value: 5);
        $backend->increment(metric: 'cache.miss', value: 3);

        $this->assertEquals(expected: 5, actual: $backend->getCounters()['cache.hit']);
        $this->assertEquals(expected: 3, actual: $backend->getCounters()['cache.miss']);
    }

    public function test_prometheus_backend_gauges() : void
    {
        $backend = new PrometheusBackend();

        $backend->gauge(metric: 'cache.hit_rate', value: 0.85);

        $this->assertEquals(expected: 0.85, actual: $backend->getGauges()['cache.hit_rate']);
    }

    public function test_prometheus_backend_histogram() : void
    {
        $backend = new PrometheusBackend();

        $backend->histogram(metric: 'cache.latency', value: 150.5);
        $backend->histogram(metric: 'cache.latency', value: 200.0);
        $backend->histogram(metric: 'cache.latency', value: 100.0);

        $this->assertCount(expectedCount: 3, haystack: $backend->getHistograms()['cache.latency']);
    }

    public function test_prometheus_render() : void
    {
        $backend = new PrometheusBackend();

        $backend->increment(metric: 'cache.hit', value: 10);
        $backend->gauge(metric: 'cache.hit_rate', value: 0.75);

        $rendered = $backend->render();

        $this->assertStringContainsString(needle: 'cache.hit 10', haystack: $rendered);
        $this->assertStringContainsString(needle: 'cache.hit_rate 0.75', haystack: $rendered);
        $this->assertStringContainsString(needle: '# TYPE cache.hit counter', haystack: $rendered);
        $this->assertStringContainsString(needle: '# TYPE cache.hit_rate gauge', haystack: $rendered);
    }

    public function test_statsd_backend_messages() : void
    {
        $backend = new StatsDBackend();

        $backend->increment(metric: 'cache.hit', value: 5);
        $backend->gauge(metric: 'cache.hit_rate', value: 0.85);
        $backend->timing(metric: 'cache.latency', milliseconds: 150);

        $messages = $backend->getMessages();

        $this->assertContains(needle: 'cache.hit:5|c', haystack: $messages);
        $this->assertContains(needle: 'cache.hit_rate:0.85|g', haystack: $messages);
        $this->assertContains(needle: 'cache.latency:150|ms', haystack: $messages);
    }

    public function test_statsd_percentile() : void
    {
        $backend = new StatsDBackend();

        $backend->timing(metric: 'cache.latency', milliseconds: 100);
        $backend->timing(metric: 'cache.latency', milliseconds: 200);
        $backend->timing(metric: 'cache.latency', milliseconds: 300);
        $backend->timing(metric: 'cache.latency', milliseconds: 400);
        $backend->timing(metric: 'cache.latency', milliseconds: 500);

        $percentile = $backend->getPercentile(percentile: 50);

        $this->assertArrayHasKey(key: 'cache.latency', array: $percentile);
        $this->assertEquals(expected: 300, actual: $percentile['cache.latency']);
    }

    public function test_metrics_sink_records_hits() : void
    {
        $backend = new PrometheusBackend();
        $sink    = new MetricsSink(backend: $backend, prefix: 'cache');

        $sink->recordHit();

        $this->assertEquals(expected: 1, actual: $backend->getCounters()['cache.hit']);
    }

    public function test_metrics_sink_records_latency() : void
    {
        $backend = new PrometheusBackend();
        $sink    = new MetricsSink(backend: $backend, prefix: 'cache');

        $sink->recordLatency(microseconds: 5000);

        $this->assertEquals(expected: [5], actual: $backend->getTimings()['cache.latency'] ?? []);
    }

    public function test_metrics_sink_records_all_metrics() : void
    {
        $backend = new PrometheusBackend();
        $sink    = new MetricsSink(backend: $backend, prefix: 'cache');

        $sink->recordHit();
        $sink->recordMiss();
        $sink->recordWrite();
        $sink->recordDelete();
        $sink->recordEviction();
        $sink->recordRefresh();
        $sink->recordStaleServed();
        $sink->recordLockWait();
        $sink->recordSourceFailure();
        $sink->recordStoreFailure();

        $this->assertEquals(expected: 1, actual: $backend->getCounters()['cache.hit']);
        $this->assertEquals(expected: 1, actual: $backend->getCounters()['cache.miss']);
        $this->assertEquals(expected: 1, actual: $backend->getCounters()['cache.write']);
        $this->assertEquals(expected: 1, actual: $backend->getCounters()['cache.stale_served']);
    }

    public function test_metrics_sink_records_cache_metrics() : void
    {
        $backend = new PrometheusBackend();
        $sink    = new MetricsSink(backend: $backend, prefix: 'cache');

        $clock   = new FrozenClock(timestamp: Timestamp::now());
        $metrics = new CacheMetrics();
        $metrics->recordHit();
        $metrics->recordHit();
        $metrics->recordHit();
        $metrics->recordMiss();

        $sink->recordMetrics(metrics: $metrics);

        $this->assertEquals(expected: 0.75, actual: $backend->getGauges()['cache.hit_rate']);
    }

    public function test_metrics_sink_flush() : void
    {
        $backend = new StatsDBackend();
        $sink    = new MetricsSink(backend: $backend, prefix: 'cache');

        $sink->recordHit();
        $sink->recordWrite();

        $this->assertNotEmpty(actual: $backend->getMessages());

        $sink->flush();

        $this->assertEmpty(actual: $backend->getMessages());
    }
}
