<?php

declare(strict_types=1);

namespace Avax\Cache\Tests\Unit\Cache\Capabilities\Storage\StoreCachedValues;

use Avax\Cache\System\AvaxCache;
use Avax\Cache\System\Capabilities\Storage\StoreCachedValues\InMemoryCacheStore;
use Avax\Cache\System\Foundation\Time\FrozenClock;
use Avax\Cache\System\Foundation\Time\Timestamp;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\InvalidArgumentException;

final class InMemoryCacheStoreTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function test_stores_and_retrieves_values() : void
    {
        $clock = new FrozenClock(timestamp: Timestamp::now());
        $store = new InMemoryCacheStore(clock: $clock);

        $cache = new AvaxCache(store: $store, clock: $clock);

        $cache->set(key: 'key', value: 'value');

        $this->assertSame(expected: 'value', actual: $cache->get(key: 'key'));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function test_returns_default_for_missing_key() : void
    {
        $clock = new FrozenClock(timestamp: Timestamp::now());
        $store = new InMemoryCacheStore(clock: $clock);

        $cache = new AvaxCache(store: $store, clock: $clock);

        $this->assertSame(expected: 'default', actual: $cache->get(key: 'missing', default: 'default'));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function test_stores_null_without_confusion() : void
    {
        $clock = new FrozenClock(timestamp: Timestamp::now());
        $store = new InMemoryCacheStore(clock: $clock);

        $cache = new AvaxCache(store: $store, clock: $clock);

        $cache->set(key: 'null-key', value: null);

        $this->assertTrue(condition: $cache->has(key: 'null-key'));
        $this->assertNull(actual: $cache->get(key: 'null-key'));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function test_deletes_value() : void
    {
        $clock = new FrozenClock(timestamp: Timestamp::now());
        $store = new InMemoryCacheStore(clock: $clock);

        $cache = new AvaxCache(store: $store, clock: $clock);

        $cache->set(key: 'delete-key', value: 'value');
        $this->assertTrue(condition: $cache->has(key: 'delete-key'));

        $cache->delete(key: 'delete-key');
        $this->assertFalse(condition: $cache->has(key: 'delete-key'));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function test_clears_all_values() : void
    {
        $clock = new FrozenClock(timestamp: Timestamp::now());
        $store = new InMemoryCacheStore(clock: $clock);

        $cache = new AvaxCache(store: $store, clock: $clock);

        $cache->set(key: 'key1', value: 'value1');
        $cache->set(key: 'key2', value: 'value2');

        $cache->clear();

        $this->assertFalse(condition: $cache->has(key: 'key1'));
        $this->assertFalse(condition: $cache->has(key: 'key2'));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function test_remembers_missing_value() : void
    {
        $clock = new FrozenClock(timestamp: Timestamp::now());
        $store = new InMemoryCacheStore(clock: $clock);

        $cache = new AvaxCache(store: $store, clock: $clock);

        $result = $cache->remember(key: 'compute-key', ttl: 3600, loader: static fn () => 'computed');

        $this->assertSame(expected: 'computed', actual: $result);
        $this->assertSame(expected: 'computed', actual: $cache->get(key: 'compute-key'));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function test_does_not_reload_existing_value() : void
    {
        $clock = new FrozenClock(timestamp: Timestamp::now());
        $store = new InMemoryCacheStore(clock: $clock);

        $cache = new AvaxCache(store: $store, clock: $clock);
        $cache->set(key: 'existing', value: 'original');

        $loadCount = 0;
        $result = $cache->remember(key: 'existing', ttl: 3600, loader: static function () use (&$loadCount) {
            $loadCount++;

            return 'loaded';
        });

        $this->assertSame(expected: 'original', actual: $result);
        $this->assertSame(expected: 0, actual: $loadCount);
    }
}