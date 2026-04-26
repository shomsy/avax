<?php

declare(strict_types=1);

namespace Avax\Cache\Tests\Unit\Cache\Capabilities\Source\SyncWithSource\SourceSyncPolicies;

use Avax\Cache\System\Capabilities\Lifecycle\CachedValues\CachedValueLifecycle;
use Avax\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Capabilities\Source\SyncWithSource\CacheSource;
use Avax\Cache\System\Capabilities\Source\SyncWithSource\CacheSourceKey;
use Avax\Cache\System\Capabilities\Source\SyncWithSource\SourceSyncPolicies\SourceSyncCoordinator;
use Avax\Cache\System\Capabilities\Source\SyncWithSource\SourceSyncPolicies\SourceSyncPolicy;
use Avax\Cache\System\Capabilities\Storage\StoreCachedValues\InMemoryCacheStore;
use Avax\Cache\System\Capabilities\Storage\StoreCachedValues\StoredCacheRecord;
use Avax\Cache\System\Foundation\Time\Duration;
use Avax\Cache\System\Foundation\Time\FrozenClock;
use Avax\Cache\System\Foundation\Time\Timestamp;
use PHPUnit\Framework\TestCase;

final class SourceSyncCoordinatorTest extends TestCase
{
    private FrozenClock        $clock;
    private InMemoryCacheStore $cache;
    private TestCacheSource    $source;

    public function test_write_through_invalidates_cache_after_source_write() : void
    {
        $coordinator = new SourceSyncCoordinator(
            source: $this->source,
            cache : $this->cache,
            policy: SourceSyncPolicy::WRITE_THROUGH
        );

        $this->cache->write(key: $this->makeKey(key: 'key_1'), record: $this->makeRecord(value: 'cached_value'));

        $coordinator->write(key: $this->makeKey(key: 'key_1'), value: 'new_value');

        $this->assertFalse(condition: $this->cache->exists(key: $this->makeKey(key: 'key_1')));
        $this->assertEquals(expected: 'new_value', actual: $this->source->get(key: 'key_1'));
    }

    private function makeKey(string $key) : CacheKey
    {
        return CacheKey::create(key: $key);
    }

    private function makeRecord(string $value) : StoredCacheRecord
    {
        $now       = $this->clock->now();
        $lifecycle = CachedValueLifecycle::create(
            createdAt: $now,
            expiresAt: $now->add(duration: Duration::ofSeconds(seconds: 3600)),
            clock    : $this->clock
        );

        return new StoredCacheRecord(value: $value, lifecycle: $lifecycle);
    }

    public function test_write_around_invalidates_cache_and_writes_to_source() : void
    {
        $coordinator = new SourceSyncCoordinator(
            source: $this->source,
            cache : $this->cache,
            policy: SourceSyncPolicy::WRITE_AROUND
        );

        $this->cache->write(key: $this->makeKey(key: 'key_1'), record: $this->makeRecord(value: 'cached_value'));

        $coordinator->write(key: $this->makeKey(key: 'key_1'), value: 'new_value');

        $this->assertFalse(condition: $this->cache->exists(key: $this->makeKey(key: 'key_1')));
        $this->assertEquals(expected: 'new_value', actual: $this->source->get(key: 'key_1'));
    }

    public function test_delete_through_invalidates_cache_after_source_delete() : void
    {
        $coordinator = new SourceSyncCoordinator(
            source: $this->source,
            cache : $this->cache,
            policy: SourceSyncPolicy::WRITE_THROUGH
        );

        $this->source->set(key: 'key_1', value: 'value');
        $this->cache->write(key: $this->makeKey(key: 'key_1'), record: $this->makeRecord(value: 'cached_value'));

        $coordinator->delete(key: $this->makeKey(key: 'key_1'));

        $this->assertFalse(condition: $this->cache->exists(key: $this->makeKey(key: 'key_1')));
        $this->assertFalse(condition: $this->source->has(key: 'key_1'));
    }

    public function test_delete_around_invalidates_cache_only() : void
    {
        $coordinator = new SourceSyncCoordinator(
            source: $this->source,
            cache : $this->cache,
            policy: SourceSyncPolicy::WRITE_AROUND
        );

        $this->source->set(key: 'key_1', value: 'value');
        $this->cache->write(key: $this->makeKey(key: 'key_1'), record: $this->makeRecord(value: 'cached_value'));

        $coordinator->delete(key: $this->makeKey(key: 'key_1'));

        $this->assertFalse(condition: $this->cache->exists(key: $this->makeKey(key: 'key_1')));
        $this->assertTrue(condition: $this->source->has(key: 'key_1'));
    }

    public function test_should_populate_cache_on_miss() : void
    {
        $coordinator = new SourceSyncCoordinator(
            source: $this->source,
            cache : $this->cache,
            policy: SourceSyncPolicy::CACHE_ASIDE
        );

        $this->assertTrue(condition: $coordinator->shouldPopulateCacheOnMiss());

        $coordinator = new SourceSyncCoordinator(
            source: $this->source,
            cache : $this->cache,
            policy: SourceSyncPolicy::WRITE_AROUND
        );

        $this->assertFalse(condition: $coordinator->shouldPopulateCacheOnMiss());
    }

    protected function setUp() : void
    {
        $this->clock  = new FrozenClock(timestamp: Timestamp::now());
        $this->cache  = new InMemoryCacheStore(clock: $this->clock, maxEntries: 100);
        $this->source = new TestCacheSource();
    }
}

final class TestCacheSource implements CacheSource
{
    /** @var array<string, mixed> */
    private array $data = [];

    public function load(CacheSourceKey $key) : mixed
    {
        return $this->data[$key->fullKey()] ?? null;
    }

    public function write(CacheSourceKey $key, mixed $value) : void
    {
        $this->data[$key->fullKey()] = $value;
    }

    public function delete(CacheSourceKey $key) : void
    {
        unset($this->data[$key->fullKey()]);
    }

    public function exists(CacheSourceKey $key) : bool
    {
        return isset($this->data[$key->fullKey()]);
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