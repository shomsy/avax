<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Health;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\StoredCacheRecord;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;
use Throwable;

/**
 * Detects cache health issues for cache stores.
 *
 * Works with any CacheStore implementation (Redis, Memcached, etc.)
 * through the adapter interface, making it testable without actual
 * cache backends.
 */
final class CacheHealthDetector
{
    /** @var array<string, CacheHealthStatus> */
    private array $statusCache = [];

    public function __construct(
        private readonly Clock $clock = new SystemClock(),
        private readonly int   $latencyThresholdMs = 100,
        private readonly float $memoryUsageThreshold = 90.0,
        private readonly float $hitRateThreshold = 0.5,
        private readonly int   $statusCacheTtlSeconds = 10
    ) {}

    /**
     * Detect health issues for a cache store.
     *
     * Runs all health checks and returns a comprehensive status.
     */
    public function detect(CacheStore $store, string $storeId = 'default') : CacheHealthStatus
    {
        $now = $this->clock->now();

        // Check cached status
        $cachedStatus = $this->getCachedStatus($storeId, $now);

        if ($cachedStatus !== null) {
            return $cachedStatus;
        }

        // Run connection check first
        $connectionStatus = $this->checkConnection($store);

        if (! $connectionStatus->connected) {
            $this->cacheStatus($storeId, $connectionStatus);

            return $connectionStatus;
        }

        // Run additional checks
        $latencyStatus = $this->checkLatency($store);
        $memoryStatus  = $this->checkMemory($store);
        $hitRateStatus = $this->checkHitRate($store);

        // Combine results - use the most severe status
        $status = $this->combineStatuses(
            connection: $connectionStatus,
            latency   : $latencyStatus,
            memory    : $memoryStatus,
            hitRate   : $hitRateStatus
        );

        $this->cacheStatus($storeId, $status);

        return $status;
    }

    /**
     * Get cached health status if still valid.
     */
    private function getCachedStatus(string $storeId, Timestamp $now) : CacheHealthStatus|null
    {
        if (! isset($this->statusCache[$storeId])) {
            return null;
        }

        $status = $this->statusCache[$storeId];
        $age    = $now->difference($status->lastCheck)->seconds;

        if ($age > $this->statusCacheTtlSeconds) {
            unset($this->statusCache[$storeId]);

            return null;
        }

        return $status;
    }

    /**
     * Check if the cache store is connected.
     */
    public function checkConnection(CacheStore $store) : CacheHealthStatus
    {
        $now       = $this->clock->now();
        $startTime = microtime(true);

        try {
            // Test connection by checking if a non-existent key returns missing
            $testKey = new CacheKey(
                '__health_check_connection__'
            );

            $result    = $store->read($testKey, $this->clock);
            $latencyMs = (int) ((microtime(true) - $startTime) * 1000);

            // If we got here, the connection is working
            return CacheHealthStatus::healthy(
                latency  : $latencyMs,
                lastCheck: $now
            );
        } catch (Throwable $e) {
            $latencyMs = (int) ((microtime(true) - $startTime) * 1000);

            return CacheHealthStatus::unhealthy(
                error    : sprintf('Connection check failed: %s', $e->getMessage()),
                latency  : $latencyMs,
                lastCheck: $now
            );
        }
    }

    /**
     * Cache a health status.
     */
    private function cacheStatus(string $storeId, CacheHealthStatus $status) : void
    {
        $this->statusCache[$storeId] = $status;
    }

    /**
     * Check the latency of the cache store.
     *
     * Performs multiple read/write operations and measures response time.
     */
    public function checkLatency(CacheStore $store, int $samples = 5) : CacheHealthStatus
    {
        $now       = $this->clock->now();
        $latencies = [];
        $errors    = [];

        for ($i = 0; $i < $samples; $i++) {
            $testKey = new CacheKey(
                sprintf('__health_check_latency_%d__%d__', $i, $now->seconds)
            );

            $startTime = microtime(true);

            try {
                // Write test
                $testRecord = StoredCacheRecord::create(
                    value: 'health_check_' . $i,
                    ttl  : 60
                );
                $store->write($testKey, $testRecord);

                // Read test
                $store->read($testKey, $this->clock);

                // Delete test
                $store->forget($testKey);

                $latency     = (int) ((microtime(true) - $startTime) * 1000);
                $latencies[] = $latency;
            } catch (Throwable $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (empty($latencies)) {
            return CacheHealthStatus::degraded(
                error    : sprintf('Latency check failed: %s', implode(', ', $errors)),
                lastCheck: $now
            );
        }

        $avgLatency = (int) (array_sum($latencies) / count($latencies));
        $maxLatency = max($latencies);

        if ($maxLatency > $this->latencyThresholdMs) {
            return CacheHealthStatus::degraded(
                error    : sprintf('High latency detected: %dms (threshold: %dms)', $maxLatency, $this->latencyThresholdMs),
                latency  : $avgLatency,
                lastCheck: $now
            );
        }

        return CacheHealthStatus::healthy(
            latency  : $avgLatency,
            lastCheck: $now
        );
    }

    /**
     * Create a new health detector with default settings.
     */
    public static function create(
        Clock|null $clock = null,
        int        $latencyThresholdMs = 100,
        float      $memoryUsageThreshold = 90.0,
        float      $hitRateThreshold = 0.5
    ) : self
    {
        return new self(
            clock               : $clock ?? new SystemClock(),
            latencyThresholdMs  : $latencyThresholdMs,
            memoryUsageThreshold: $memoryUsageThreshold,
            hitRateThreshold    : $hitRateThreshold
        );
    }

    /**
     * Check memory usage of the cache store.
     *
     * Note: This requires the cache store to support memory stats.
     * For stores that don't support it, returns a status with 0 memory usage.
     */
    public function checkMemory(CacheStore $store) : CacheHealthStatus
    {
        $now = $this->clock->now();

        try {
            // Try to get memory stats if the store supports it
            $memoryInfo = $this->getMemoryInfo($store);

            if ($memoryInfo === null) {
                return CacheHealthStatus::healthy(
                    latency    : 0,
                    memoryUsage: 0.0,
                    lastCheck  : $now
                );
            }

            $usagePercent = $memoryInfo['usagePercent'];
            $memoryUsed   = $memoryInfo['used'];
            $memoryLimit  = $memoryInfo['limit'];

            if ($usagePercent > $this->memoryUsageThreshold) {
                return CacheHealthStatus::degraded(
                    error      : sprintf(
                                     'High memory usage: %.1f%% (%d / %d bytes)',
                                     $usagePercent,
                                     $memoryUsed,
                                     $memoryLimit
                                 ),
                    memoryUsage: $usagePercent,
                    memoryLimit: $memoryLimit,
                    keyCount   : $memoryInfo['keyCount'] ?? 0,
                    lastCheck  : $now
                );
            }

            return CacheHealthStatus::healthy(
                latency    : 0,
                memoryUsage: $usagePercent,
                memoryLimit: $memoryLimit,
                keyCount   : $memoryInfo['keyCount'] ?? 0,
                lastCheck  : $now
            );
        } catch (Throwable $e) {
            return CacheHealthStatus::degraded(
                error    : sprintf('Memory check failed: %s', $e->getMessage()),
                lastCheck: $now
            );
        }
    }

    /**
     * Get memory information from the cache store.
     *
     * Override this method in a subclass to support specific cache backends.
     *
     * @return array{used: int, limit: int, usagePercent: float, keyCount: int}|null
     */
    protected function getMemoryInfo(CacheStore $store) : array|null
    {
        // Default implementation returns null (not supported)
        // Subclasses can override for Redis/Memcached specific implementations
        return null;
    }

    /**
     * Check the hit rate of the cache store.
     *
     * Note: This requires the cache store to support hit/miss stats.
     * For stores that don't support it, returns a status with default hit rate.
     */
    public function checkHitRate(CacheStore $store) : CacheHealthStatus
    {
        $now = $this->clock->now();

        try {
            $hitRate = $this->getHitRate($store);

            if ($hitRate === null) {
                return CacheHealthStatus::healthy(
                    latency  : 0,
                    hitRate  : 1.0,
                    lastCheck: $now
                );
            }

            if ($hitRate < $this->hitRateThreshold) {
                return CacheHealthStatus::degraded(
                    error    : sprintf(
                                   'Low hit rate: %.1f%% (threshold: %.1f%%)',
                                   $hitRate * 100,
                                   $this->hitRateThreshold * 100
                               ),
                    hitRate  : $hitRate,
                    lastCheck: $now
                );
            }

            return CacheHealthStatus::healthy(
                latency  : 0,
                hitRate  : $hitRate,
                lastCheck: $now
            );
        } catch (Throwable $e) {
            return CacheHealthStatus::degraded(
                error    : sprintf('Hit rate check failed: %s', $e->getMessage()),
                lastCheck: $now
            );
        }
    }

    /**
     * Get hit rate from the cache store.
     *
     * Override this method in a subclass to support specific cache backends.
     */
    protected function getHitRate(CacheStore $store) : float|null
    {
        // Default implementation returns null (not supported)
        // Subclasses can override for Redis/Memcached specific implementations
        return null;
    }

    /**
     * Combine multiple health statuses into one.
     */
    private function combineStatuses(
        CacheHealthStatus $connection,
        CacheHealthStatus $latency,
        CacheHealthStatus $memory,
        CacheHealthStatus $hitRate
    ) : CacheHealthStatus
    {
        // If not connected, return connection status
        if (! $connection->connected) {
            return $connection;
        }

        // Find the most severe error
        $errors = [];

        if ($latency->error !== null) {
            $errors[] = $latency->error;
        }

        if ($memory->error !== null) {
            $errors[] = $memory->error;
        }

        if ($hitRate->error !== null) {
            $errors[] = $hitRate->error;
        }

        $error = empty($errors) ? null : implode('; ', $errors);

        // Use the worst metrics
        $hasIssues = $error !== null;

        if ($hasIssues) {
            return CacheHealthStatus::degraded(
                error      : $error,
                latency    : max($latency->latency, $connection->latency),
                memoryUsage: $memory->memoryUsage,
                hitRate    : $hitRate->hitRate,
                memoryLimit: $memory->memoryLimit,
                keyCount   : $memory->keyCount,
                lastCheck  : $connection->lastCheck
            );
        }

        return CacheHealthStatus::healthy(
            latency        : max($latency->latency, $connection->latency),
            memoryUsage    : $memory->memoryUsage,
            hitRate        : $hitRate->hitRate,
            memoryLimit    : $memory->memoryLimit,
            keyCount       : $memory->keyCount,
            connectionCount: $connection->connectionCount,
            version        : $connection->version,
            lastCheck      : $connection->lastCheck
        );
    }

    /**
     * Clear the cached status for a store.
     */
    public function clearCachedStatus(string $storeId) : void
    {
        unset($this->statusCache[$storeId]);
    }

    /**
     * Clear all cached statuses.
     */
    public function clearAllCachedStatuses() : void
    {
        $this->statusCache = [];
    }
}
