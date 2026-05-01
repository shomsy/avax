<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Cache\Unit\Cache\PublicSurface;

use Avax\Components\Application\Cache\System\AvaxCache;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\InMemoryCacheStore;
use Avax\Components\Application\Cache\System\Foundation\Time\FrozenClock;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;
use Avax\Tests\TestCase;
use Psr\SimpleCache\InvalidArgumentException;

final class RememberNullRegressionTest extends TestCase
{
    private FrozenClock $clock;
    private InMemoryCacheStore $store;
    private AvaxCache   $cache;

    /**
     * @throws InvalidArgumentException
     */
    public function test_it_does_not_reload_when_cached_value_is_null() : void
    {
        $this->cache->set(key: 'nullable', value: null);

        $loadCount = 0;

        $result = $this->cache->remember(key: 'nullable', ttl: 3600, loader: static function () use (&$loadCount) {
            $loadCount++;

            return 'loaded';
        });

        $this->assertNull(actual: $result);
        $this->assertSame(expected: 0, actual: $loadCount);
    }

    public function test_it_does_reload_when_key_does_not_exist() : void
    {
        $loadCount = 0;

        $result = $this->cache->remember(key: 'missing', ttl: 3600, loader: static function () use (&$loadCount) {
            $loadCount++;

            return 'loaded';
        });

        $this->assertSame(expected: 'loaded', actual: $result);
        $this->assertSame(expected: 1, actual: $loadCount);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function test_it_reloads_when_value_is_not_null() : void
    {
        $this->cache->set(key: 'exists', value: 'not_null');

        $loadCount = 0;

        $result = $this->cache->remember(key: 'exists', ttl: 3600, loader: static function () use (&$loadCount) {
            $loadCount++;

            return 'loaded';
        });

        $this->assertSame(expected: 'not_null', actual: $result);
        $this->assertSame(expected: 0, actual: $loadCount);
    }

    protected function setUp() : void
    {
        parent::setUp();
        $this->clock = new FrozenClock(timestamp: Timestamp::now());
        $this->store = new InMemoryCacheStore(
            clock: $this->clock,
        );
        $this->cache = new AvaxCache(
            store: $this->store,
            clock: $this->clock,
        );
    }
}
