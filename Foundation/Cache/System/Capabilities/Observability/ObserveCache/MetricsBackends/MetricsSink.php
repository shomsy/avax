<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Observability\ObserveCache\MetricsBackends;

use Avax\Cache\System\Capabilities\Observability\ObserveCache\CacheMetrics;

final class MetricsSink
{
    public function __construct(
        private MetricsBackend $backend,
        private string|null    $prefix = 'cache'
    ) {}

    public function recordHit() : void
    {
        $this->backend->increment(metric: "{$this->prefix}.hit");
    }

    public function recordMiss() : void
    {
        $this->backend->increment(metric: "{$this->prefix}.miss");
    }

    public function recordWrite() : void
    {
        $this->backend->increment(metric: "{$this->prefix}.write");
    }

    public function recordDelete() : void
    {
        $this->backend->increment(metric: "{$this->prefix}.delete");
    }

    public function recordEviction() : void
    {
        $this->backend->increment(metric: "{$this->prefix}.eviction");
    }

    public function recordRefresh() : void
    {
        $this->backend->increment(metric: "{$this->prefix}.refresh");
    }

    public function recordStaleServed() : void
    {
        $this->backend->increment(metric: "{$this->prefix}.stale_served");
    }

    public function recordLockWait() : void
    {
        $this->backend->increment(metric: "{$this->prefix}.lock_wait");
    }

    public function recordSourceFailure() : void
    {
        $this->backend->increment(metric: "{$this->prefix}.source_failure");
    }

    public function recordStoreFailure() : void
    {
        $this->backend->increment(metric: "{$this->prefix}.store_failure");
    }

    public function recordLatency(int $microseconds) : void
    {
        $this->backend->timing(metric: "{$this->prefix}.latency", milliseconds: (int) ($microseconds / 1000));
    }

    public function recordMetrics(CacheMetrics $metrics) : void
    {
        $this->backend->gauge(metric: "{$this->prefix}.hit_rate", value: $metrics->hitRate());
        $this->backend->gauge(metric: "{$this->prefix}.miss_rate", value: $metrics->missRate());
        $this->backend->gauge(metric: "{$this->prefix}.average_latency_ms", value: $metrics->averageLatencyMicroseconds() / 1000);
        $this->backend->gauge(metric: "{$this->prefix}.total_operations", value: (float) $metrics->totalOperations());
    }

    public function flush() : void
    {
        $this->backend->flush();
    }
}