<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Observability\ObserveCache;

final class CacheMetrics
{
    private int $hits                     = 0;
    private int $misses                   = 0;
    private int $writes                   = 0;
    private int $deletes                  = 0;
    private int $evictions                = 0;
    private int $invalidations            = 0;
    private int $refreshes                = 0;
    private int $staleServed              = 0;
    private int $lockWaits                = 0;
    private int $sourceFailures           = 0;
    private int $storeFailures            = 0;
    private int $totalLatencyMicroseconds = 0;
    private int $operationCount           = 0;

    public function recordHit() : void
    {
        $this->hits++;
    }

    public function recordMiss() : void
    {
        $this->misses++;
    }

    public function recordWrite() : void
    {
        $this->writes++;
    }

    public function recordDelete() : void
    {
        $this->deletes++;
    }

    public function recordEviction() : void
    {
        $this->evictions++;
    }

    public function recordInvalidation() : void
    {
        $this->invalidations++;
    }

    public function recordRefresh() : void
    {
        $this->refreshes++;
    }

    public function recordStaleServed() : void
    {
        $this->staleServed++;
    }

    public function recordLockWait() : void
    {
        $this->lockWaits++;
    }

    public function recordSourceFailure() : void
    {
        $this->sourceFailures++;
    }

    public function recordStoreFailure() : void
    {
        $this->storeFailures++;
    }

    public function recordLatency(int $microseconds) : void
    {
        $this->totalLatencyMicroseconds += $microseconds;
        $this->operationCount++;
    }

    public function toArray() : array
    {
        return [
            'hits'               => $this->hits,
            'misses'             => $this->misses,
            'writes'             => $this->writes,
            'deletes'            => $this->deletes,
            'evictions'          => $this->evictions,
            'invalidations'      => $this->invalidations,
            'refreshes'          => $this->refreshes,
            'stale_served'       => $this->staleServed,
            'lock_waits'         => $this->lockWaits,
            'source_failures'    => $this->sourceFailures,
            'store_failures'     => $this->storeFailures,
            'hit_rate'           => $this->hitRate(),
            'miss_rate'          => $this->missRate(),
            'average_latency_ms' => $this->averageLatencyMicroseconds() / 1000,
            'total_operations'   => $this->totalOperations(),
        ];
    }

    public function hitRate() : float
    {
        $total = $this->hits + $this->misses;

        if ($total === 0) {
            return 0.0;
        }

        return $this->hits / $total;
    }

    public function missRate() : float
    {
        return 1.0 - $this->hitRate();
    }

    public function averageLatencyMicroseconds() : float
    {
        if ($this->operationCount === 0) {
            return 0.0;
        }

        return $this->totalLatencyMicroseconds / $this->operationCount;
    }

    public function totalOperations() : int
    {
        return $this->hits + $this->misses + $this->writes + $this->deletes;
    }
}