<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Cache\Capabilities\Storage\StoreCachedValues;

use Avax\Components\Application\Cache\System\AvaxCache;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\InMemoryCacheStore;
use Avax\Components\Application\Cache\System\Foundation\Time\FrozenClock;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;
use PHPUnit\Framework\TestCase;

final class InMemoryCacheStoreTest extends TestCase
{
    public function test_stores_and_retrieves_values() : void
    {
        $frozenClock        = new FrozenClock(timestamp: Timestamp::now());
        $inMemoryCacheStore = new InMemoryCacheStore(clock: $frozenClock);

        $avaxCache = new AvaxCache(clock: $frozenClock, cacheStore: $inMemoryCacheStore);

        $avaxCache->set(key: 'key', value: 'value');

        $this->assertSame('value', $avaxCache->get(key: 'key'));
    }

    public function test_returns_default_for_missing_key() : void
    {
        $frozenClock        = new FrozenClock(timestamp: Timestamp::now());
        $inMemoryCacheStore = new InMemoryCacheStore(clock: $frozenClock);

        $avaxCache = new AvaxCache(clock: $frozenClock, cacheStore: $inMemoryCacheStore);

        $this->assertSame('default', $avaxCache->get(key: 'missing', default: 'default'));
    }

    public function test_stores_null_without_confusion() : void
    {
        $frozenClock        = new FrozenClock(timestamp: Timestamp::now());
        $inMemoryCacheStore = new InMemoryCacheStore(clock: $frozenClock);

        $avaxCache = new AvaxCache(clock: $frozenClock, cacheStore: $inMemoryCacheStore);

        $avaxCache->set(key: 'null-key', value: null);

        $this->assertTrue($avaxCache->has(key: 'null-key'));
        $this->assertNull($avaxCache->get(key: 'null-key'));
    }

    public function test_deletes_value() : void
    {
        $frozenClock        = new FrozenClock(timestamp: Timestamp::now());
        $inMemoryCacheStore = new InMemoryCacheStore(clock: $frozenClock);

        $avaxCache = new AvaxCache(clock: $frozenClock, cacheStore: $inMemoryCacheStore);

        $avaxCache->set(key: 'delete-key', value: 'value');
        $this->assertTrue($avaxCache->has(key: 'delete-key'));

        $avaxCache->delete(key: 'delete-key');
        $this->assertFalse($avaxCache->has(key: 'delete-key'));
    }

    public function test_clears_all_values() : void
    {
        $frozenClock        = new FrozenClock(timestamp: Timestamp::now());
        $inMemoryCacheStore = new InMemoryCacheStore(clock: $frozenClock);

        $avaxCache = new AvaxCache(clock: $frozenClock, cacheStore: $inMemoryCacheStore);

        $avaxCache->set(key: 'key1', value: 'value1');
        $avaxCache->set(key: 'key2', value: 'value2');

        $avaxCache->clear();

        $this->assertFalse($avaxCache->has(key: 'key1'));
        $this->assertFalse($avaxCache->has(key: 'key2'));
    }

    public function test_remembers_missing_value() : void
    {
        $frozenClock        = new FrozenClock(timestamp: Timestamp::now());
        $inMemoryCacheStore = new InMemoryCacheStore(clock: $frozenClock);

        $avaxCache = new AvaxCache(clock: $frozenClock, cacheStore: $inMemoryCacheStore);

        $result = $avaxCache->remember(key: 'compute-key', ttl: 3600, loader: static fn () : string => 'computed');

        $this->assertSame('computed', $result);
        $this->assertSame('computed', $avaxCache->get(key: 'compute-key'));
    }

    public function test_does_not_reload_existing_value() : void
    {
        $frozenClock        = new FrozenClock(timestamp: Timestamp::now());
        $inMemoryCacheStore = new InMemoryCacheStore(clock: $frozenClock);

        $avaxCache = new AvaxCache(clock: $frozenClock, cacheStore: $inMemoryCacheStore);
        $avaxCache->set(key: 'existing', value: 'original');

        $loadCount = 0;
        $result    = $avaxCache->remember(key: 'existing', ttl: 3600, loader: static function () use (&$loadCount) : string {
            $loadCount++;

            return 'loaded';
        });

        $this->assertSame('original', $result);
        $this->assertSame(0, $loadCount);
    }
}
