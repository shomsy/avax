<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Cache\Capabilities\Storage\StoreCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\CachedValues\CachedValueLifecycle;
use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\FirstInFirstOutReplacement;
use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\LeastRecentlyUsedReplacement;
use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\NoReplacement;
use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Capabilities\Storage\SizeCachedValues\CacheCapacityWasExceeded;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\InMemoryCacheStore;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\StoredCacheRecord;
use Avax\Components\Application\Cache\System\Foundation\Time\Duration;
use Avax\Components\Application\Cache\System\Foundation\Time\FrozenClock;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;
use Override;
use PHPUnit\Framework\TestCase;

final class InMemoryCacheStoreCapacityTest extends TestCase
{
    private FrozenClock $frozenClock;

    public function test_store_respects_max_entries() : void
    {
        $inMemoryCacheStore = new InMemoryCacheStore(
            clock     : $this->frozenClock,
            maxEntries: 3,
        );

        for ($i = 1; $i <= 3; $i++) {
            $inMemoryCacheStore->write(
                cacheKey         : $this->makeKey(key: 'key_' . $i),
                storedCacheRecord: $this->makeRecord(value: 'value_' . $i, ttlSeconds: 3600),
            );
        }

        $this->assertSame(3, $inMemoryCacheStore->count());
    }

    private function makeKey(string $key) : CacheKey
    {
        return CacheKey::create(key: $key);
    }

    private function makeRecord(mixed $value, int $ttlSeconds) : StoredCacheRecord
    {
        $now       = $this->frozenClock->now();
        $timestamp = $now->add(duration: Duration::ofSeconds(seconds: $ttlSeconds));

        $cachedValueLifecycle = CachedValueLifecycle::create(
            createdAt: $now,
            expiresAt: $timestamp,
            clock    : $this->frozenClock,
        );

        return new StoredCacheRecord(
            value               : $value,
            cachedValueLifecycle: $cachedValueLifecycle,
        );
    }

    public function test_lru_eviction_respects_access_order() : void
    {
        $inMemoryCacheStore = new InMemoryCacheStore(
            clock                          : $this->frozenClock,
            maxEntries                     : 3,
            chooseCachedValueForReplacement: new LeastRecentlyUsedReplacement(),
        );

        $inMemoryCacheStore->write(cacheKey: $this->makeKey(key: 'key_1'), storedCacheRecord: $this->makeRecord(value: 'value_1', ttlSeconds: 3600));
        $inMemoryCacheStore->write(cacheKey: $this->makeKey(key: 'key_2'), storedCacheRecord: $this->makeRecord(value: 'value_2', ttlSeconds: 3600));
        $inMemoryCacheStore->write(cacheKey: $this->makeKey(key: 'key_3'), storedCacheRecord: $this->makeRecord(value: 'value_3', ttlSeconds: 3600));

        $this->frozenClock->moveForward(duration: Duration::ofSeconds(seconds: 1));
        $inMemoryCacheStore->read(clock: $this->frozenClock, cacheKey: $this->makeKey(key: 'key_1'));

        $this->frozenClock->moveForward(duration: Duration::ofSeconds(seconds: 1));
        $inMemoryCacheStore->write(cacheKey: $this->makeKey(key: 'key_4'), storedCacheRecord: $this->makeRecord(value: 'value_4', ttlSeconds: 3600));

        $this->assertTrue($inMemoryCacheStore->exists(cacheKey: $this->makeKey(key: 'key_1')));
        $this->assertFalse($inMemoryCacheStore->exists(cacheKey: $this->makeKey(key: 'key_2')));
        $this->assertTrue($inMemoryCacheStore->exists(cacheKey: $this->makeKey(key: 'key_3')));
        $this->assertTrue($inMemoryCacheStore->exists(cacheKey: $this->makeKey(key: 'key_4')));
    }

    public function test_fifo_eviction_respects_creation_order() : void
    {
        $inMemoryCacheStore = new InMemoryCacheStore(
            clock                          : $this->frozenClock,
            maxEntries                     : 3,
            chooseCachedValueForReplacement: new FirstInFirstOutReplacement(),
        );

        $inMemoryCacheStore->write(cacheKey: $this->makeKey(key: 'key_1'), storedCacheRecord: $this->makeRecord(value: 'value_1', ttlSeconds: 3600));

        $this->frozenClock->moveForward(duration: Duration::ofSeconds(seconds: 1));
        $inMemoryCacheStore->write(cacheKey: $this->makeKey(key: 'key_2'), storedCacheRecord: $this->makeRecord(value: 'value_2', ttlSeconds: 3600));
        $this->frozenClock->moveForward(duration: Duration::ofSeconds(seconds: 1));
        $inMemoryCacheStore->write(cacheKey: $this->makeKey(key: 'key_3'), storedCacheRecord: $this->makeRecord(value: 'value_3', ttlSeconds: 3600));

        $inMemoryCacheStore->write(cacheKey: $this->makeKey(key: 'key_4'), storedCacheRecord: $this->makeRecord(value: 'value_4', ttlSeconds: 3600));

        $this->assertFalse($inMemoryCacheStore->exists(cacheKey: $this->makeKey(key: 'key_1')));
        $this->assertTrue($inMemoryCacheStore->exists(cacheKey: $this->makeKey(key: 'key_2')));
        $this->assertTrue($inMemoryCacheStore->exists(cacheKey: $this->makeKey(key: 'key_3')));
        $this->assertTrue($inMemoryCacheStore->exists(cacheKey: $this->makeKey(key: 'key_4')));
    }

    public function test_expired_entries_are_evicted_first() : void
    {
        $inMemoryCacheStore = new InMemoryCacheStore(
            clock     : $this->frozenClock,
            maxEntries: 3,
        );

        $inMemoryCacheStore->write(cacheKey: $this->makeKey(key: 'key_1'), storedCacheRecord: $this->makeRecord(value: 'value_1', ttlSeconds: 1));

        $this->frozenClock->moveForward(duration: Duration::ofSeconds(seconds: 2));

        $inMemoryCacheStore->write(cacheKey: $this->makeKey(key: 'key_2'), storedCacheRecord: $this->makeRecord(value: 'value_2', ttlSeconds: 3600));
        $inMemoryCacheStore->write(cacheKey: $this->makeKey(key: 'key_3'), storedCacheRecord: $this->makeRecord(value: 'value_3', ttlSeconds: 3600));

        $this->assertSame(3, $inMemoryCacheStore->count());

        $inMemoryCacheStore->write(cacheKey: $this->makeKey(key: 'key_4'), storedCacheRecord: $this->makeRecord(value: 'value_4', ttlSeconds: 3600));

        $this->assertFalse($inMemoryCacheStore->exists(cacheKey: $this->makeKey(key: 'key_1')));
        $this->assertTrue($inMemoryCacheStore->exists(cacheKey: $this->makeKey(key: 'key_2')));
        $this->assertTrue($inMemoryCacheStore->exists(cacheKey: $this->makeKey(key: 'key_3')));
        $this->assertTrue($inMemoryCacheStore->exists(cacheKey: $this->makeKey(key: 'key_4')));
    }

    public function test_no_replacement_policy_throws_when_capacity_exceeded() : void
    {
        $inMemoryCacheStore = new InMemoryCacheStore(
            clock                          : $this->frozenClock,
            maxEntries                     : 2,
            chooseCachedValueForReplacement: new NoReplacement(),
        );

        $inMemoryCacheStore->write(cacheKey: $this->makeKey(key: 'key_1'), storedCacheRecord: $this->makeRecord(value: 'value_1', ttlSeconds: 3600));
        $inMemoryCacheStore->write(cacheKey: $this->makeKey(key: 'key_2'), storedCacheRecord: $this->makeRecord(value: 'value_2', ttlSeconds: 3600));

        $this->expectException(CacheCapacityWasExceeded::class);
        $inMemoryCacheStore->write(cacheKey: $this->makeKey(key: 'key_3'), storedCacheRecord: $this->makeRecord(value: 'value_3', ttlSeconds: 3600));
    }

    #[Override]
    protected function setUp() : void
    {
        $this->frozenClock = new FrozenClock(timestamp: Timestamp::now());
    }
}
