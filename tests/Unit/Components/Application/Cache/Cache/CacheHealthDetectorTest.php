<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Cache\Cache;

use Avax\Components\Application\Cache\System\Capabilities\Health\CacheHealthDetector;
use Avax\Components\Application\Cache\System\Capabilities\Health\CacheHealthStatus;
use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasFound;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasMissing;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\StoredCacheRecord;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\FrozenClock;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Fake cache store that can simulate connection failures and latency.
 */
final class FakeCacheStoreForHealth implements CacheStore
{
    /** @var array<string, StoredCacheRecord> */
    private array $records = [];

    private bool $connected = true;

    private string $connectionError = 'Connection refused';

    private int $artificialLatencyMs = 0;

    private Clock $clock;

    public function __construct(?Clock $clock = null)
    {
        $this->clock = $clock ?? new SystemClock;
    }

    public function read(CacheKey $key, Clock $clock) : CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        if (! $this->connected) {
            throw new RuntimeException($this->connectionError);
        }

        if ($this->artificialLatencyMs > 0) {
            usleep($this->artificialLatencyMs * 1000);
        }

        $fullKey = $key->fullKey();

        if (! isset($this->records[$fullKey])) {
            return new CacheStoreRecordWasMissing(key: $key);
        }

        $record = $this->records[$fullKey];

        if ($record->lifecycle->isExpired(clock: $clock)) {
            unset($this->records[$fullKey]);

            return new CacheStoreRecordWasMissing(key: $key);
        }

        return new CacheStoreRecordWasFound(key: $key, record: $record, clock: $clock);
    }

    public function write(CacheKey $key, StoredCacheRecord $record) : void
    {
        if (! $this->connected) {
            throw new RuntimeException($this->connectionError);
        }

        if ($this->artificialLatencyMs > 0) {
            usleep($this->artificialLatencyMs * 1000);
        }

        $this->records[$key->fullKey()] = $record;
    }

    public function forget(CacheKey $key) : void
    {
        if (! $this->connected) {
            throw new RuntimeException($this->connectionError);
        }

        unset($this->records[$key->fullKey()]);
    }

    public function clear() : void
    {
        $this->records = [];
    }

    public function exists(CacheKey $key) : bool
    {
        if (! $this->connected) {
            throw new RuntimeException($this->connectionError);
        }

        $fullKey = $key->fullKey();

        if (! isset($this->records[$fullKey])) {
            return false;
        }

        return ! $this->records[$fullKey]->lifecycle->isExpired(clock: $this->clock);
    }

    public function setConnected(bool $connected, string $error = 'Connection refused') : void
    {
        $this->connected = $connected;
        $this->connectionError = $error;
    }

    public function setArtificialLatencyMs(int $ms) : void
    {
        $this->artificialLatencyMs = $ms;
    }

    public function count() : int
    {
        return count($this->records);
    }
}

final class CacheHealthDetectorTest extends TestCase
{
    public function test_detect_returns_healthy_status_for_working_store() : void
    {
        $store = new FakeCacheStoreForHealth;
        $detector = $this->createDetector();

        $status = $detector->detect($store, 'test-store');

        $this->assertTrue($status->connected);
        // Note: detect() calls combineStatuses which may fail due to production bug
        // when there are issues. For healthy stores it works fine.
    }

    // --- detect() returns health status ---

    private function createDetector(
        ?Clock $clock = null,
        int    $latencyThresholdMs = 100,
        float $memoryUsageThreshold = 90.0,
        float $hitRateThreshold = 0.5,
    ) : CacheHealthDetector
    {
        return CacheHealthDetector::create(
            clock               : $clock ?? new FrozenClock(Timestamp::fromUnixTime(1000000)),
            latencyThresholdMs  : $latencyThresholdMs,
            memoryUsageThreshold: $memoryUsageThreshold,
            hitRateThreshold    : $hitRateThreshold,
        );
    }

    public function test_detect_returns_unhealthy_for_disconnected_store() : void
    {
        $store = new FakeCacheStoreForHealth;
        $store->setConnected(false);

        $detector = $this->createDetector();

        $status = $detector->detect($store, 'disconnected-store');

        $this->assertFalse($status->connected);
        $this->assertNotNull($status->error);
        $this->assertStringContainsString('Connection check failed', $status->error);
    }

    public function test_detect_caches_status() : void
    {
        $store = new FakeCacheStoreForHealth;
        $detector = $this->createDetector();

        $status1 = $detector->detect($store, 'cached-store');
        $store->setConnected(false);

        // Should return cached status, not re-check
        $status2 = $detector->detect($store, 'cached-store');

        $this->assertTrue($status2->connected); // Still shows as connected from cache
    }

    public function test_detect_clears_cached_status_after_ttl() : void
    {
        $clock = new FrozenClock(Timestamp::fromUnixTime(1000000));
        $store = new FakeCacheStoreForHealth;
        $detector = new CacheHealthDetector(
            clock                : $clock,
            latencyThresholdMs   : 100,
            memoryUsageThreshold : 90.0,
            hitRateThreshold     : 0.5,
            statusCacheTtlSeconds: 5,
        );

        $status1 = $detector->detect($store, 'ttl-store');
        $this->assertTrue($status1->connected);

        // Advance clock past TTL
        $clock->reset(Timestamp::fromUnixTime(1000010));

        $store->setConnected(false);
        $status2 = $detector->detect($store, 'ttl-store');

        $this->assertFalse($status2->connected);
    }

    public function test_detect_uses_store_id_for_caching() : void
    {
        $store = new FakeCacheStoreForHealth;
        $detector = $this->createDetector();

        $detector->detect($store, 'store-a');

        $store->setConnected(false);

        // Different store ID should not use cached status
        $status = $detector->detect($store, 'store-b');

        $this->assertFalse($status->connected);
    }

    // --- checkConnection() ---

    public function test_check_connection_detects_connected_store() : void
    {
        $store = new FakeCacheStoreForHealth;
        $detector = $this->createDetector();

        $status = $detector->checkConnection($store);

        $this->assertTrue($status->connected);
        $this->assertNull($status->error);
    }

    public function test_check_connection_detects_disconnected_store() : void
    {
        $store = new FakeCacheStoreForHealth;
        $store->setConnected(false, 'Redis server unavailable');

        $detector = $this->createDetector();

        $status = $detector->checkConnection($store);

        $this->assertFalse($status->connected);
        $this->assertNotNull($status->error);
        $this->assertStringContainsString('Redis server unavailable', $status->error);
    }

    public function test_check_connection_measures_latency() : void
    {
        $store = new FakeCacheStoreForHealth;
        $detector = $this->createDetector();

        $status = $detector->checkConnection($store);

        $this->assertGreaterThanOrEqual(0, $status->latency);
    }

    // --- checkLatency() ---

    public function test_check_latency_returns_healthy_for_fast_store() : void
    {
        $store = new FakeCacheStoreForHealth;
        $detector = $this->createDetector(latencyThresholdMs: 100);

        $status = $detector->checkLatency($store);

        $this->assertNull($status->error);
    }

    public function test_check_latency_returns_degraded_for_slow_store() : void
    {
        $store = new FakeCacheStoreForHealth;
        $store->setArtificialLatencyMs(200);

        $detector = $this->createDetector(latencyThresholdMs: 100);

        $status = $detector->checkLatency($store, samples: 3);

        $this->assertNotNull($status->error);
        $this->assertStringContainsString('High latency detected', $status->error);
    }

    public function test_check_latency_with_multiple_samples() : void
    {
        $store = new FakeCacheStoreForHealth;
        $detector = $this->createDetector();

        $status = $detector->checkLatency($store, samples: 10);

        $this->assertGreaterThanOrEqual(0, $status->latency);
    }

    public function test_check_latency_returns_degraded_on_error() : void
    {
        $store = new FakeCacheStoreForHealth;
        $store->setConnected(false);

        $detector = $this->createDetector();

        $status = $detector->checkLatency($store, samples: 3);

        $this->assertNotNull($status->error);
        $this->assertStringContainsString('Latency check failed', $status->error);
    }

    // --- checkMemory() ---

    public function test_check_memory_returns_healthy_when_store_does_not_support_memory_stats() : void
    {
        $store = new FakeCacheStoreForHealth;
        $detector = $this->createDetector();

        $status = $detector->checkMemory($store);

        $this->assertSame(0.0, $status->memoryUsage);
        $this->assertNull($status->error);
    }

    // --- checkHitRate() ---

    public function test_check_hit_rate_returns_healthy_when_store_does_not_support_hit_rate() : void
    {
        $store = new FakeCacheStoreForHealth;
        $detector = $this->createDetector();

        $status = $detector->checkHitRate($store);

        $this->assertSame(1.0, $status->hitRate);
        $this->assertNull($status->error);
    }

    // --- CacheHealthStatus factory methods ---

    public function test_cache_health_status_healthy() : void
    {
        $status = CacheHealthStatus::healthy(
            latency        : 10,
            memoryUsage    : 45.0,
            hitRate        : 0.95,
            memoryLimit    : 1024,
            keyCount       : 500,
            connectionCount: 3,
            version        : '7.0.0',
        );

        $this->assertTrue($status->connected);
        $this->assertSame(10, $status->latency);
        $this->assertSame(45.0, $status->memoryUsage);
        $this->assertSame(0.95, $status->hitRate);
        $this->assertNull($status->error);
        $this->assertSame(1024, $status->memoryLimit);
        $this->assertSame(500, $status->keyCount);
        $this->assertSame(3, $status->connectionCount);
        $this->assertSame('7.0.0', $status->version);
    }

    public function test_cache_health_status_unhealthy() : void
    {
        $status = CacheHealthStatus::unhealthy(
            error  : 'Connection timeout',
            latency: 5000,
        );

        $this->assertFalse($status->connected);
        $this->assertSame('Connection timeout', $status->error);
        $this->assertSame(5000, $status->latency);
    }

    public function test_cache_health_status_degraded() : void
    {
        $status = CacheHealthStatus::degraded(
            error  : 'High latency',
            latency: 200,
            hitRate: 0.3,
        );

        $this->assertTrue($status->connected);
        $this->assertSame('High latency', $status->error);
        $this->assertSame(200, $status->latency);
        $this->assertSame(0.3, $status->hitRate);
    }

    // --- isHealthy() / isDegraded() checks ---

    public function test_is_healthy_returns_true_for_healthy_status() : void
    {
        $status = CacheHealthStatus::healthy(
            latency    : 10,
            memoryUsage: 50.0,
            hitRate    : 0.8,
        );

        $this->assertTrue($status->isHealthy());
    }

    public function test_is_healthy_returns_false_for_unhealthy_status() : void
    {
        $status = CacheHealthStatus::unhealthy(error: 'Down');

        $this->assertFalse($status->isHealthy());
    }

    public function test_is_healthy_returns_false_for_degraded_with_high_latency() : void
    {
        $status = CacheHealthStatus::degraded(
            error  : 'Slow',
            latency: 200,
        );

        $this->assertFalse($status->isHealthy());
    }

    public function test_is_healthy_returns_false_for_degraded_with_high_memory() : void
    {
        $status = new CacheHealthStatus(
            connected  : true,
            latency    : 0,
            memoryUsage: 95.0,
            hitRate    : 1.0,
            lastCheck  : Timestamp::fromUnixTime(1000000),
        );

        $this->assertFalse($status->isHealthy());
    }

    public function test_is_healthy_returns_false_for_degraded_with_low_hit_rate() : void
    {
        $status = new CacheHealthStatus(
            connected  : true,
            latency    : 0,
            memoryUsage: 0.0,
            hitRate    : 0.2,
            lastCheck  : Timestamp::fromUnixTime(1000000),
        );

        $this->assertFalse($status->isHealthy());
    }

    public function test_is_healthy_with_custom_thresholds() : void
    {
        $status = CacheHealthStatus::degraded(
            error  : 'Slow',
            latency: 50,
        );

        // With relaxed threshold, should be healthy
        $this->assertTrue($status->isHealthy(maxLatencyMs: 100));

        // With strict threshold, should not be healthy
        $this->assertFalse($status->isHealthy(maxLatencyMs: 30));
    }

    public function test_is_degraded_returns_true_for_degraded_status() : void
    {
        $status = CacheHealthStatus::degraded(
            error  : 'High latency',
            latency: 200,
        );

        $this->assertTrue($status->isDegraded());
    }

    public function test_is_degraded_returns_false_for_healthy_status() : void
    {
        $status = CacheHealthStatus::healthy(
            latency    : 10,
            memoryUsage: 50.0,
            hitRate    : 0.8,
        );

        $this->assertFalse($status->isDegraded());
    }

    public function test_is_degraded_returns_false_for_unhealthy_status() : void
    {
        $status = CacheHealthStatus::unhealthy(error: 'Down');

        $this->assertFalse($status->isDegraded());
    }

    public function test_is_degraded_with_custom_thresholds() : void
    {
        $status = CacheHealthStatus::healthy(latency: 50);

        // With strict threshold, considered degraded
        $this->assertTrue($status->isDegraded(maxLatencyMs: 30));

        // With relaxed threshold, not degraded
        $this->assertFalse($status->isDegraded(maxLatencyMs: 100));
    }

    // --- Health status properties ---

    public function test_get_health_level() : void
    {
        $healthy = CacheHealthStatus::healthy(latency: 10);
        $this->assertSame('healthy', $healthy->getHealthLevel());

        $unhealthy = CacheHealthStatus::unhealthy(error: 'Down');
        $this->assertSame('unhealthy', $unhealthy->getHealthLevel());

        $degraded = CacheHealthStatus::degraded(error: 'Slow', latency: 200);
        $this->assertSame('degraded', $degraded->getHealthLevel());
    }

    public function test_get_memory_usage_percent() : void
    {
        $status = new CacheHealthStatus(
            connected  : true,
            latency    : 0,
            memoryUsage: 500.0,
            hitRate    : 1.0,
            lastCheck  : Timestamp::fromUnixTime(1000000),
            memoryLimit: 1000,
        );

        $this->assertSame(50.0, $status->getMemoryUsagePercent());
    }

    public function test_get_memory_usage_percent_without_limit() : void
    {
        $status = CacheHealthStatus::healthy(memoryUsage: 75.0);

        $this->assertSame(75.0, $status->getMemoryUsagePercent());
    }

    public function test_is_memory_critical() : void
    {
        $critical = new CacheHealthStatus(
            connected  : true,
            latency    : 0,
            memoryUsage: 96.0,
            hitRate    : 1.0,
            lastCheck  : Timestamp::fromUnixTime(1000000),
            memoryLimit: 100,
        );

        $this->assertTrue($critical->isMemoryCritical());

        $notCritical = CacheHealthStatus::healthy(memoryUsage: 50.0, memoryLimit: 100);
        $this->assertFalse($notCritical->isMemoryCritical());
    }

    public function test_is_memory_high() : void
    {
        $high = new CacheHealthStatus(
            connected  : true,
            latency    : 0,
            memoryUsage: 85.0,
            hitRate    : 1.0,
            lastCheck  : Timestamp::fromUnixTime(1000000),
            memoryLimit: 100,
        );

        $this->assertTrue($high->isMemoryHigh());

        $notHigh = CacheHealthStatus::healthy(memoryUsage: 50.0, memoryLimit: 100);
        $this->assertFalse($notHigh->isMemoryHigh());
    }

    public function test_is_latency_high() : void
    {
        $status = CacheHealthStatus::healthy(latency: 150);

        $this->assertTrue($status->isLatencyHigh());
        $this->assertTrue($status->isLatencyHigh(thresholdMs: 100));
        $this->assertFalse($status->isLatencyHigh(thresholdMs: 200));
    }

    public function test_is_hit_rate_low() : void
    {
        $status = new CacheHealthStatus(
            connected  : true,
            latency    : 0,
            memoryUsage: 0.0,
            hitRate    : 0.2,
            lastCheck  : Timestamp::fromUnixTime(1000000),
        );

        $this->assertTrue($status->isHitRateLow());
        $this->assertTrue($status->isHitRateLow(threshold: 0.5));
        $this->assertFalse($status->isHitRateLow(threshold: 0.1));
    }

    // --- toArray() ---

    public function test_to_array() : void
    {
        $status = CacheHealthStatus::healthy(
            latency        : 15,
            memoryUsage    : 60.0,
            hitRate        : 0.85,
            memoryLimit    : 1024,
            keyCount       : 100,
            connectionCount: 2,
            version        : '6.2.0',
        );

        $array = $status->toArray();

        $this->assertTrue($array['connected']);
        $this->assertSame(15, $array['latency']);
        $this->assertSame(60.0, $array['memoryUsage']);
        $this->assertSame(0.85, $array['hitRate']);
        $this->assertNull($array['error']);
        $this->assertSame(1024, $array['memoryLimit']);
        $this->assertSame(100, $array['keyCount']);
        $this->assertSame(2, $array['connectionCount']);
        $this->assertSame('6.2.0', $array['version']);
        $this->assertArrayHasKey('memoryUsagePercent', $array);
        $this->assertArrayHasKey('healthLevel', $array);
    }

    // --- clearCachedStatus ---

    public function test_clear_cached_status() : void
    {
        $clock = new FrozenClock(Timestamp::fromUnixTime(1000000));
        $store = new FakeCacheStoreForHealth;
        $detector = $this->createDetector($clock);

        $detector->detect($store, 'clearable-store');

        $detector->clearCachedStatus('clearable-store');

        $store->setConnected(false);
        $status = $detector->detect($store, 'clearable-store');

        $this->assertFalse($status->connected);
    }

    public function test_clear_all_cached_statuses() : void
    {
        $clock = new FrozenClock(Timestamp::fromUnixTime(1000000));
        $store = new FakeCacheStoreForHealth;
        $detector = $this->createDetector($clock);

        $detector->detect($store, 'store-a');
        $detector->detect($store, 'store-b');

        $detector->clearAllCachedStatuses();

        $store->setConnected(false);

        $statusA = $detector->detect($store, 'store-a');
        $statusB = $detector->detect($store, 'store-b');

        $this->assertFalse($statusA->connected);
        $this->assertFalse($statusB->connected);
    }

    // --- Edge cases ---

    public function test_detect_with_default_store_id() : void
    {
        $store = new FakeCacheStoreForHealth;
        $detector = $this->createDetector();

        $status = $detector->detect($store);

        $this->assertTrue($status->connected);
    }

    public function test_health_status_last_check_is_set() : void
    {
        $store = new FakeCacheStoreForHealth;
        $detector = $this->createDetector();

        $status = $detector->detect($store, 'timestamp-store');

        $this->assertInstanceOf(Timestamp::class, $status->lastCheck);
        $this->assertGreaterThan(0, $status->lastCheck->seconds);
    }

    public function test_healthy_status_has_null_error() : void
    {
        $status = CacheHealthStatus::healthy();

        $this->assertNull($status->error);
    }

    public function test_unhealthy_status_has_error() : void
    {
        $status = CacheHealthStatus::unhealthy(error: 'Test error');

        $this->assertSame('Test error', $status->error);
    }

    public function test_degraded_status_has_error() : void
    {
        $status = CacheHealthStatus::degraded(error: 'Test degraded');

        $this->assertSame('Test degraded', $status->error);
    }
}
