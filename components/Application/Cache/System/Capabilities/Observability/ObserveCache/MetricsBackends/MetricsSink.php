<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Observability\ObserveCache\MetricsBackends;

use Avax\Components\Application\Cache\System\Capabilities\Observability\ObserveCache\CacheMetrics;

final readonly class MetricsSink
{
    public function __construct(private MetricsBackend $metricsBackend, private ?string $prefix = 'cache')
    {
    }

    public function recordHit(): void
    {
        $this->metricsBackend->increment(metric: $this->prefix.'.hit');
    }

    public function recordMiss(): void
    {
        $this->metricsBackend->increment(metric: $this->prefix.'.miss');
    }

    public function recordWrite(): void
    {
        $this->metricsBackend->increment(metric: $this->prefix.'.write');
    }

    public function recordDelete(): void
    {
        $this->metricsBackend->increment(metric: $this->prefix.'.delete');
    }

    public function recordEviction(): void
    {
        $this->metricsBackend->increment(metric: $this->prefix.'.eviction');
    }

    public function recordRefresh(): void
    {
        $this->metricsBackend->increment(metric: $this->prefix.'.refresh');
    }

    public function recordStaleServed(): void
    {
        $this->metricsBackend->increment(metric: $this->prefix.'.stale_served');
    }

    public function recordLockWait(): void
    {
        $this->metricsBackend->increment(metric: $this->prefix.'.lock_wait');
    }

    public function recordSourceFailure(): void
    {
        $this->metricsBackend->increment(metric: $this->prefix.'.source_failure');
    }

    public function recordStoreFailure(): void
    {
        $this->metricsBackend->increment(metric: $this->prefix.'.store_failure');
    }

    public function recordLatency(int $microseconds): void
    {
        $this->metricsBackend->timing(metric: $this->prefix.'.latency', milliseconds: (int) ($microseconds / 1000));
    }

    public function recordMetrics(CacheMetrics $cacheMetrics): void
    {
        $this->metricsBackend->gauge(metric: $this->prefix.'.hit_rate', value: $cacheMetrics->hitRate());
        $this->metricsBackend->gauge(metric: $this->prefix.'.miss_rate', value: $cacheMetrics->missRate());
        $this->metricsBackend->gauge(metric: $this->prefix.'.average_latency_ms', value: $cacheMetrics->averageLatencyMicroseconds() / 1000);
        $this->metricsBackend->gauge(metric: $this->prefix.'.total_operations', value: (float) $cacheMetrics->totalOperations());
    }

    public function flush(): void
    {
        $this->metricsBackend->flush();
    }
}
