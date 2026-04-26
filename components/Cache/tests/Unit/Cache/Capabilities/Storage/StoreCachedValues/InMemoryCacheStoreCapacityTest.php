<?php

declare(strict_types=1);

namespace Avax\Cache\Tests\Unit\Cache\Capabilities\Storage\StoreCachedValues;

use Avax\Cache\System\Capabilities\Lifecycle\CachedValues\CachedValueLifecycle;
use Avax\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\FirstInFirstOutReplacement;
use Avax\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\LeastRecentlyUsedReplacement;
use Avax\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\NoReplacement;
use Avax\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Capabilities\Storage\SizeCachedValues\CacheCapacityWasExceeded;
use Avax\Cache\System\Capabilities\Storage\StoreCachedValues\InMemoryCacheStore;
use Avax\Cache\System\Capabilities\Storage\StoreCachedValues\StoredCacheRecord;
use Avax\Cache\System\Foundation\Time\Duration;
use Avax\Cache\System\Foundation\Time\FrozenClock;
use Avax\Cache\System\Foundation\Time\Timestamp;
use PHPUnit\Framework\TestCase;

final class InMemoryCacheStoreCapacityTest extends TestCase
{
    private FrozenClock $clock;

    public function test_store_respects_max_entries() : void
    {
        $store = new InMemoryCacheStore(
            clock     : $this->clock,
            maxEntries: 3
        );

        for ($i = 1; $i <= 3; $i++) {
            $store->write(
                key   : $this->makeKey(key: "key_{$i}"),
                record: $this->makeRecord(value: "value_{$i}", ttlSeconds: 3600)
            );
        }

        $this->assertSame(expected: 3, actual: $store->count());
    }

    private function makeKey(string $key) : CacheKey
    {
        return CacheKey::create(key: $key);
    }

    private function makeRecord(mixed $value, int $ttlSeconds) : StoredCacheRecord
    {
        $now       = $this->clock->now();
        $expiresAt = $now->add(duration: Duration::ofSeconds(seconds: $ttlSeconds));

        $lifecycle = CachedValueLifecycle::create(
            createdAt: $now,
            expiresAt: $expiresAt,
            clock    : $this->clock
        );

        return new StoredCacheRecord(
            value    : $value,
            lifecycle: $lifecycle
        );
    }

    public function test_lru_eviction_respects_access_order() : void
    {
        $store = new InMemoryCacheStore(
            clock            : $this->clock,
            maxEntries       : 3,
            replacementPolicy: new LeastRecentlyUsedReplacement()
        );

        $store->write(key: $this->makeKey(key: 'key_1'), record: $this->makeRecord(value: 'value_1', ttlSeconds: 3600));
        $store->write(key: $this->makeKey(key: 'key_2'), record: $this->makeRecord(value: 'value_2', ttlSeconds: 3600));
        $store->write(key: $this->makeKey(key: 'key_3'), record: $this->makeRecord(value: 'value_3', ttlSeconds: 3600));

        $this->clock->moveForward(duration: Duration::ofSeconds(seconds: 1));
        $store->read(key: $this->makeKey(key: 'key_1'), clock: $this->clock);

        $this->clock->moveForward(duration: Duration::ofSeconds(seconds: 1));
        $store->write(key: $this->makeKey(key: 'key_4'), record: $this->makeRecord(value: 'value_4', ttlSeconds: 3600));

        $this->assertTrue(condition: $store->exists(key: $this->makeKey(key: 'key_1')));
        $this->assertFalse(condition: $store->exists(key: $this->makeKey(key: 'key_2')));
        $this->assertTrue(condition: $store->exists(key: $this->makeKey(key: 'key_3')));
        $this->assertTrue(condition: $store->exists(key: $this->makeKey(key: 'key_4')));
    }

    public function test_fifo_eviction_respects_creation_order() : void
    {
        $store = new InMemoryCacheStore(
            clock            : $this->clock,
            maxEntries       : 3,
            replacementPolicy: new FirstInFirstOutReplacement()
        );

        $store->write(key: $this->makeKey(key: 'key_1'), record: $this->makeRecord(value: 'value_1', ttlSeconds: 3600));
        $this->clock->moveForward(duration: Duration::ofSeconds(seconds: 1));
        $store->write(key: $this->makeKey(key: 'key_2'), record: $this->makeRecord(value: 'value_2', ttlSeconds: 3600));
        $this->clock->moveForward(duration: Duration::ofSeconds(seconds: 1));
        $store->write(key: $this->makeKey(key: 'key_3'), record: $this->makeRecord(value: 'value_3', ttlSeconds: 3600));

        $store->write(key: $this->makeKey(key: 'key_4'), record: $this->makeRecord(value: 'value_4', ttlSeconds: 3600));

        $this->assertFalse(condition: $store->exists(key: $this->makeKey(key: 'key_1')));
        $this->assertTrue(condition: $store->exists(key: $this->makeKey(key: 'key_2')));
        $this->assertTrue(condition: $store->exists(key: $this->makeKey(key: 'key_3')));
        $this->assertTrue(condition: $store->exists(key: $this->makeKey(key: 'key_4')));
    }

    public function test_expired_entries_are_evicted_first() : void
    {
        $store = new InMemoryCacheStore(
            clock     : $this->clock,
            maxEntries: 3
        );

        $store->write(key: $this->makeKey(key: 'key_1'), record: $this->makeRecord(value: 'value_1', ttlSeconds: 1));
        $this->clock->moveForward(duration: Duration::ofSeconds(seconds: 2));

        $store->write(key: $this->makeKey(key: 'key_2'), record: $this->makeRecord(value: 'value_2', ttlSeconds: 3600));
        $store->write(key: $this->makeKey(key: 'key_3'), record: $this->makeRecord(value: 'value_3', ttlSeconds: 3600));

        $this->assertSame(expected: 3, actual: $store->count());

        $store->write(key: $this->makeKey(key: 'key_4'), record: $this->makeRecord(value: 'value_4', ttlSeconds: 3600));

        $this->assertFalse(condition: $store->exists(key: $this->makeKey(key: 'key_1')));
        $this->assertTrue(condition: $store->exists(key: $this->makeKey(key: 'key_2')));
        $this->assertTrue(condition: $store->exists(key: $this->makeKey(key: 'key_3')));
        $this->assertTrue(condition: $store->exists(key: $this->makeKey(key: 'key_4')));
    }

    public function test_no_replacement_policy_throws_when_capacity_exceeded() : void
    {
        $store = new InMemoryCacheStore(
            clock            : $this->clock,
            maxEntries       : 2,
            replacementPolicy: new NoReplacement()
        );

        $store->write(key: $this->makeKey(key: 'key_1'), record: $this->makeRecord(value: 'value_1', ttlSeconds: 3600));
        $store->write(key: $this->makeKey(key: 'key_2'), record: $this->makeRecord(value: 'value_2', ttlSeconds: 3600));

        $this->expectException(exception: CacheCapacityWasExceeded::class);
        $store->write(key: $this->makeKey(key: 'key_3'), record: $this->makeRecord(value: 'value_3', ttlSeconds: 3600));
    }

    protected function setUp() : void
    {
        $this->clock = new FrozenClock(timestamp: Timestamp::now());
    }
}