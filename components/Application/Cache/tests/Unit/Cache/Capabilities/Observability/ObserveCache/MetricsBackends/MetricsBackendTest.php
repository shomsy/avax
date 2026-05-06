<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\tests\Unit\Cache\Capabilities\Observability\ObserveCache\MetricsBackends;

use Avax\Components\Application\Cache\System\Capabilities\Observability\ObserveCache\CacheMetrics;
use Avax\Components\Application\Cache\System\Capabilities\Observability\ObserveCache\MetricsBackends\MetricsSink;
use Avax\Components\Application\Cache\System\Capabilities\Observability\ObserveCache\MetricsBackends\PrometheusBackend;
use Avax\Components\Application\Cache\System\Capabilities\Observability\ObserveCache\MetricsBackends\StatsDBackend;
use Avax\Components\Application\Cache\System\Foundation\Time\FrozenClock;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;
use PHPUnit\Framework\TestCase;

final class MetricsBackendTest extends TestCase
{
    public function test_prometheus_backend_counts(): void
    {
        $prometheusBackend = new PrometheusBackend();

        $prometheusBackend->increment(metric: 'cache.hit', value: 5);
        $prometheusBackend->increment(metric: 'cache.miss', value: 3);

        $this->assertEquals(expected: 5, actual: $prometheusBackend->getCounters()['cache.hit']);
        $this->assertEquals(expected: 3, actual: $prometheusBackend->getCounters()['cache.miss']);
    }

    public function test_prometheus_backend_gauges(): void
    {
        $prometheusBackend = new PrometheusBackend();

        $prometheusBackend->gauge(metric: 'cache.hit_rate', value: 0.85);

        $this->assertEquals(expected: 0.85, actual: $prometheusBackend->getGauges()['cache.hit_rate']);
    }

    public function test_prometheus_backend_histogram(): void
    {
        $prometheusBackend = new PrometheusBackend();

        $prometheusBackend->histogram(metric: 'cache.latency', value: 150.5);
        $prometheusBackend->histogram(metric: 'cache.latency', value: 200.0);
        $prometheusBackend->histogram(metric: 'cache.latency', value: 100.0);

        $this->assertCount(expectedCount: 3, haystack: $prometheusBackend->getHistograms()['cache.latency']);
    }

    public function test_prometheus_render(): void
    {
        $prometheusBackend = new PrometheusBackend();

        $prometheusBackend->increment(metric: 'cache.hit', value: 10);
        $prometheusBackend->gauge(metric: 'cache.hit_rate', value: 0.75);

        $rendered = $prometheusBackend->render();

        $this->assertStringContainsString(needle: 'cache.hit 10', haystack: $rendered);
        $this->assertStringContainsString(needle: 'cache.hit_rate 0.75', haystack: $rendered);
        $this->assertStringContainsString(needle: '# TYPE cache.hit counter', haystack: $rendered);
        $this->assertStringContainsString(needle: '# TYPE cache.hit_rate gauge', haystack: $rendered);
    }

    public function test_statsd_backend_messages(): void
    {
        $statsDBackend = new StatsDBackend();

        $statsDBackend->increment(metric: 'cache.hit', value: 5);
        $statsDBackend->gauge(metric: 'cache.hit_rate', value: 0.85);
        $statsDBackend->timing(metric: 'cache.latency', milliseconds: 150);

        $messages = $statsDBackend->getMessages();

        $this->assertContains(needle: 'cache.hit:5|c', haystack: $messages);
        $this->assertContains(needle: 'cache.hit_rate:0.85|g', haystack: $messages);
        $this->assertContains(needle: 'cache.latency:150|ms', haystack: $messages);
    }

    public function test_statsd_percentile(): void
    {
        $statsDBackend = new StatsDBackend();

        $statsDBackend->timing(metric: 'cache.latency', milliseconds: 100);
        $statsDBackend->timing(metric: 'cache.latency', milliseconds: 200);
        $statsDBackend->timing(metric: 'cache.latency', milliseconds: 300);
        $statsDBackend->timing(metric: 'cache.latency', milliseconds: 400);
        $statsDBackend->timing(metric: 'cache.latency', milliseconds: 500);

        $percentile = $statsDBackend->getPercentile(percentile: 50);

        $this->assertArrayHasKey(key: 'cache.latency', array: $percentile);
        $this->assertEquals(expected: 300, actual: $percentile['cache.latency']);
    }

    public function test_metrics_sink_records_hits(): void
    {
        $prometheusBackend = new PrometheusBackend();
        $metricsSink = new MetricsSink(prefix: 'cache', backend: $prometheusBackend);

        $metricsSink->recordHit();

        $this->assertEquals(expected: 1, actual: $prometheusBackend->getCounters()['cache.hit']);
    }

    public function test_metrics_sink_records_latency(): void
    {
        $prometheusBackend = new PrometheusBackend();
        $metricsSink = new MetricsSink(prefix: 'cache', backend: $prometheusBackend);

        $metricsSink->recordLatency(microseconds: 5000);

        $this->assertEquals(expected: [5], actual: $prometheusBackend->getTimings()['cache.latency'] ?? []);
    }

    public function test_metrics_sink_records_all_metrics(): void
    {
        $prometheusBackend = new PrometheusBackend();
        $metricsSink = new MetricsSink(prefix: 'cache', backend: $prometheusBackend);

        $metricsSink->recordHit();
        $metricsSink->recordMiss();
        $metricsSink->recordWrite();
        $metricsSink->recordDelete();
        $metricsSink->recordEviction();
        $metricsSink->recordRefresh();
        $metricsSink->recordStaleServed();
        $metricsSink->recordLockWait();
        $metricsSink->recordSourceFailure();
        $metricsSink->recordStoreFailure();

        $this->assertEquals(expected: 1, actual: $prometheusBackend->getCounters()['cache.hit']);
        $this->assertEquals(expected: 1, actual: $prometheusBackend->getCounters()['cache.miss']);
        $this->assertEquals(expected: 1, actual: $prometheusBackend->getCounters()['cache.write']);
        $this->assertEquals(expected: 1, actual: $prometheusBackend->getCounters()['cache.stale_served']);
    }

    public function test_metrics_sink_records_cache_metrics(): void
    {
        $prometheusBackend = new PrometheusBackend();
        $metricsSink = new MetricsSink(prefix: 'cache', backend: $prometheusBackend);

        new FrozenClock(timestamp: Timestamp::now());
        $cacheMetrics = new CacheMetrics();
        $cacheMetrics->recordHit();
        $cacheMetrics->recordHit();
        $cacheMetrics->recordHit();
        $cacheMetrics->recordMiss();

        $metricsSink->recordMetrics(metrics: $cacheMetrics);

        $this->assertEquals(expected: 0.75, actual: $prometheusBackend->getGauges()['cache.hit_rate']);
    }

    public function test_metrics_sink_flush(): void
    {
        $statsDBackend = new StatsDBackend();
        $metricsSink = new MetricsSink(prefix: 'cache', backend: $statsDBackend);

        $metricsSink->recordHit();
        $metricsSink->recordWrite();

        $this->assertNotEmpty(actual: $statsDBackend->getMessages());

        $metricsSink->flush();

        $this->assertEmpty(actual: $statsDBackend->getMessages());
    }
}
