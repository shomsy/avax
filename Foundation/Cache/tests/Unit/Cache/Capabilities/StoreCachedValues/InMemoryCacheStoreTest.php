<?php

declare(strict_types=1);

namespace Avax\Cache\Tests\Unit\Cache\Capabilities\StoreCachedValues;

use Avax\Cache\System\AvaxCache;
use Avax\Cache\System\Capabilities\StoreCachedValues\InMemoryCacheStore;
use Avax\Cache\System\Foundation\Time\FrozenClock;
use Avax\Cache\System\Foundation\Time\Timestamp;
use PHPUnit\Framework\TestCase;

final class InMemoryCacheStoreTest extends TestCase
{
    public function test_stores_and_retrieves_values() : void
    {
        $clock = new FrozenClock(Timestamp::now());
        $store = new InMemoryCacheStore($clock);

        $cache = new AvaxCache($store, $clock);

        $cache->set('key', 'value');

        $this->assertSame('value', $cache->get('key'));
    }

    public function test_returns_default_for_missing_key() : void
    {
        $clock = new FrozenClock(Timestamp::now());
        $store = new InMemoryCacheStore($clock);

        $cache = new AvaxCache($store, $clock);

        $this->assertSame('default', $cache->get('missing', 'default'));
    }

    public function test_stores_null_without_confusion() : void
    {
        $clock = new FrozenClock(Timestamp::now());
        $store = new InMemoryCacheStore($clock);

        $cache = new AvaxCache($store, $clock);

        $cache->set('null-key', null);

        $this->assertTrue($cache->has('null-key'));
        $this->assertNull($cache->get('null-key'));
    }

    public function test_deletes_value() : void
    {
        $clock = new FrozenClock(Timestamp::now());
        $store = new InMemoryCacheStore($clock);

        $cache = new AvaxCache($store, $clock);

        $cache->set('delete-key', 'value');
        $this->assertTrue($cache->has('delete-key'));

        $cache->delete('delete-key');
        $this->assertFalse($cache->has('delete-key'));
    }

    public function test_clears_all_values() : void
    {
        $clock = new FrozenClock(Timestamp::now());
        $store = new InMemoryCacheStore($clock);

        $cache = new AvaxCache($store, $clock);

        $cache->set('key1', 'value1');
        $cache->set('key2', 'value2');

        $cache->clear();

        $this->assertFalse($cache->has('key1'));
        $this->assertFalse($cache->has('key2'));
    }

    public function test_remembers_missing_value() : void
    {
        $clock = new FrozenClock(Timestamp::now());
        $store = new InMemoryCacheStore($clock);

        $cache = new AvaxCache($store, $clock);

        $result = $cache->remember('compute-key', ttl: 3600, loader: fn () => 'computed');

        $this->assertSame('computed', $result);
        $this->assertSame('computed', $cache->get('compute-key'));
    }

    public function test_does_not_reload_existing_value() : void
    {
        $clock = new FrozenClock(Timestamp::now());
        $store = new InMemoryCacheStore($clock);

        $cache = new AvaxCache($store, $clock);
        $cache->set('existing', 'original');

        $loadCount = 0;
        $result    = $cache->remember('existing', ttl: 3600, loader: function () use (&$loadCount) {
            $loadCount++;

            return 'loaded';
        });

        $this->assertSame('original', $result);
        $this->assertSame(0, $loadCount);
    }
}