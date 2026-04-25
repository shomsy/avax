<?php

declare(strict_types=1);

namespace Avax\Cache\Tests\Unit\Cache\PublicSurface;

use Avax\Cache\System\CacheContract;
use Avax\Cache\System\Foundation\Time\FrozenClock;
use Avax\Cache\System\Foundation\Time\SystemClock;
use Avax\Cache\System\Capabilities\StoreCachedValues\InMemoryCacheStore;
use Avax\Cache\System\AvaxCache;
use PHPUnit\Framework\TestCase;

final class RememberNullRegressionTest extends TestCase
{
    private FrozenClock        $clock;
    private InMemoryCacheStore $store;
    private AvaxCache          $cache;

    protected function setUp() : void
    {
        $this->clock = new FrozenClock(new SystemClock());
        $this->store = new InMemoryCacheStore(
            maxEntries: 100,
            clock     : $this->clock
        );
        $this->cache = new AvaxCache(
            store     : $this->store,
            clock     : $this->clock,
            defaultTtl: 3600
        );
    }

    public function test_it_does_not_reload_when_cached_value_is_null() : void
    {
        $this->cache->set('nullable', null);

        $loadCount = 0;

        $result = $this->cache->remember('nullable', ttl: 3600, loader: function () use (&$loadCount) {
            $loadCount++;

            return 'loaded';
        });

        $this->assertNull($result);
        $this->assertSame(0, $loadCount);
    }

    public function test_it_does_reload_when_key_does_not_exist() : void
    {
        $loadCount = 0;

        $result = $this->cache->remember('missing', ttl: 3600, loader: function () use (&$loadCount) {
            $loadCount++;

            return 'loaded';
        });

        $this->assertSame('loaded', $result);
        $this->assertSame(1, $loadCount);
    }

    public function test_it_reloads_when_value_is_not_null() : void
    {
        $this->cache->set('exists', 'not_null');

        $loadCount = 0;

        $result = $this->cache->remember('exists', ttl: 3600, loader: function () use (&$loadCount) {
            $loadCount++;

            return 'loaded';
        });

        $this->assertSame('not_null', $result);
        $this->assertSame(0, $loadCount);
    }
}