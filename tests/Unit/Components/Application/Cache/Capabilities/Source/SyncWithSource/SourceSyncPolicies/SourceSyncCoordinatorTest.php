<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Cache\Capabilities\Source\SyncWithSource\SourceSyncPolicies;

use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\CachedValues\CachedValueLifecycle;
use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Capabilities\Source\SyncWithSource\CacheSource;
use Avax\Components\Application\Cache\System\Capabilities\Source\SyncWithSource\CacheSourceKey;
use Avax\Components\Application\Cache\System\Capabilities\Source\SyncWithSource\SourceSyncPolicies\SourceSyncCoordinator;
use Avax\Components\Application\Cache\System\Capabilities\Source\SyncWithSource\SourceSyncPolicies\SourceSyncPolicy;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\InMemoryCacheStore;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\StoredCacheRecord;
use Avax\Components\Application\Cache\System\Foundation\Time\Duration;
use Avax\Components\Application\Cache\System\Foundation\Time\FrozenClock;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;
use Override;
use PHPUnit\Framework\TestCase;

final class SourceSyncCoordinatorTest extends TestCase
{
    private FrozenClock $frozenClock;

    private InMemoryCacheStore $inMemoryCacheStore;

    private TestCacheSource $testCacheSource;

    public function test_write_through_invalidates_cache_after_source_write() : void
    {
        $sourceSyncCoordinator = new SourceSyncCoordinator(
            cacheSource     : $this->testCacheSource,
            cacheStore      : $this->inMemoryCacheStore,
            sourceSyncPolicy: SourceSyncPolicy::WRITE_THROUGH,
        );

        $this->inMemoryCacheStore->write(cacheKey: $this->makeKey(key: 'key_1'), storedCacheRecord: $this->makeRecord(value: 'cached_value'));

        $sourceSyncCoordinator->write(value: 'new_value', cacheKey: $this->makeKey(key: 'key_1'));

        $this->assertFalse($this->inMemoryCacheStore->exists(cacheKey: $this->makeKey(key: 'key_1')));
        $this->assertEquals('new_value', $this->testCacheSource->get(key: 'key_1'));
    }

    private function makeKey(string $key) : CacheKey
    {
        return CacheKey::create(key: $key);
    }

    private function makeRecord(string $value) : StoredCacheRecord
    {
        $now                  = $this->frozenClock->now();
        $cachedValueLifecycle = CachedValueLifecycle::create(
            createdAt: $now,
            expiresAt: $now->add(duration: Duration::ofSeconds(seconds: 3600)),
            clock    : $this->frozenClock,
        );

        return new StoredCacheRecord(value: $value, cachedValueLifecycle: $cachedValueLifecycle);
    }

    public function test_write_around_invalidates_cache_and_writes_to_source() : void
    {
        $sourceSyncCoordinator = new SourceSyncCoordinator(
            cacheSource     : $this->testCacheSource,
            cacheStore      : $this->inMemoryCacheStore,
            sourceSyncPolicy: SourceSyncPolicy::WRITE_AROUND,
        );

        $this->inMemoryCacheStore->write(cacheKey: $this->makeKey(key: 'key_1'), storedCacheRecord: $this->makeRecord(value: 'cached_value'));

        $sourceSyncCoordinator->write(value: 'new_value', cacheKey: $this->makeKey(key: 'key_1'));

        $this->assertFalse($this->inMemoryCacheStore->exists(cacheKey: $this->makeKey(key: 'key_1')));
        $this->assertEquals('new_value', $this->testCacheSource->get(key: 'key_1'));
    }

    public function test_delete_through_invalidates_cache_after_source_delete() : void
    {
        $sourceSyncCoordinator = new SourceSyncCoordinator(
            cacheSource     : $this->testCacheSource,
            cacheStore      : $this->inMemoryCacheStore,
            sourceSyncPolicy: SourceSyncPolicy::WRITE_THROUGH,
        );

        $this->testCacheSource->set(key: 'key_1', value: 'value');
        $this->inMemoryCacheStore->write(cacheKey: $this->makeKey(key: 'key_1'), storedCacheRecord: $this->makeRecord(value: 'cached_value'));

        $sourceSyncCoordinator->delete(cacheKey: $this->makeKey(key: 'key_1'));

        $this->assertFalse($this->inMemoryCacheStore->exists(cacheKey: $this->makeKey(key: 'key_1')));
        $this->assertFalse($this->testCacheSource->has(key: 'key_1'));
    }

    public function test_delete_around_invalidates_cache_only() : void
    {
        $sourceSyncCoordinator = new SourceSyncCoordinator(
            cacheSource     : $this->testCacheSource,
            cacheStore      : $this->inMemoryCacheStore,
            sourceSyncPolicy: SourceSyncPolicy::WRITE_AROUND,
        );

        $this->testCacheSource->set(key: 'key_1', value: 'value');
        $this->inMemoryCacheStore->write(cacheKey: $this->makeKey(key: 'key_1'), storedCacheRecord: $this->makeRecord(value: 'cached_value'));

        $sourceSyncCoordinator->delete(cacheKey: $this->makeKey(key: 'key_1'));

        $this->assertFalse($this->inMemoryCacheStore->exists(cacheKey: $this->makeKey(key: 'key_1')));
        $this->assertTrue($this->testCacheSource->has(key: 'key_1'));
    }

    public function test_should_populate_cache_on_miss() : void
    {
        $coordinator = new SourceSyncCoordinator(
            cacheSource     : $this->testCacheSource,
            cacheStore      : $this->inMemoryCacheStore,
            sourceSyncPolicy: SourceSyncPolicy::CACHE_ASIDE,
        );

        $this->assertTrue($coordinator->shouldPopulateCacheOnMiss());

        $coordinator = new SourceSyncCoordinator(
            cacheSource     : $this->testCacheSource,
            cacheStore      : $this->inMemoryCacheStore,
            sourceSyncPolicy: SourceSyncPolicy::WRITE_AROUND,
        );

        $this->assertFalse($coordinator->shouldPopulateCacheOnMiss());
    }

    #[Override]
    protected function setUp() : void
    {
        $this->frozenClock        = new FrozenClock(timestamp: Timestamp::now());
        $this->inMemoryCacheStore = new InMemoryCacheStore(clock: $this->frozenClock, maxEntries: 100);
        $this->testCacheSource    = new TestCacheSource();
    }
}

final class TestCacheSource implements CacheSource
{
    /** @var array<string, mixed> */
    private array $data = [];

    #[Override]
    public function load(CacheSourceKey $cacheSourceKey) : mixed
    {
        return $this->data[$cacheSourceKey->fullKey()] ?? null;
    }

    #[Override]
    public function write(CacheSourceKey $cacheSourceKey, mixed $value) : void
    {
        $this->data[$cacheSourceKey->fullKey()] = $value;
    }

    #[Override]
    public function delete(CacheSourceKey $cacheSourceKey) : void
    {
        unset($this->data[$cacheSourceKey->fullKey()]);
    }

    #[Override]
    public function exists(CacheSourceKey $cacheSourceKey) : bool
    {
        return isset($this->data[$cacheSourceKey->fullKey()]);
    }

    public function get(string $key) : mixed
    {
        return $this->data[$key] ?? null;
    }

    public function set(string $key, mixed $value) : void
    {
        $this->data[$key] = $value;
    }

    public function has(string $key) : bool
    {
        return isset($this->data[$key]);
    }
}
