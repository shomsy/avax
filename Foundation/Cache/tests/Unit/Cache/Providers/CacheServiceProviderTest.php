<?php

declare(strict_types=1);

namespace Avax\Cache\Tests\Unit\Cache\Providers;

use Avax\Cache\Cache;
use Avax\Cache\CompiledCache;
use Avax\Cache\System\AvaxCache;
use Avax\Cache\System\CacheContract;
use Avax\Cache\System\Capabilities\StoreCachedValues\InMemoryCacheStore;
use Avax\Cache\System\Foundation\Time\FrozenClock;
use Avax\Cache\System\PublicSurface\CacheNotConfigured;
use PHPUnit\Framework\TestCase;

final class CacheServiceProviderTest extends TestCase
{
    private FrozenClock        $clock;
    private InMemoryCacheStore $defaultStore;
    private InMemoryCacheStore $apiStore;
    private CacheContract      $defaultCache;
    private CacheContract      $apiCache;

    public function test_cache_store_with_name_requires_provider() : void
    {
        Cache::reset();

        $this->expectException(CacheNotConfigured::class);
        $this->expectExceptionMessage('Named store requires CacheServiceProvider');

        Cache::store('api');
    }

    public function test_cache_store_returns_default() : void
    {
        Cache::use($this->defaultCache);

        $store = Cache::store();

        $this->assertSame($this->defaultCache, $store);
    }

    public function test_cache_use_sets_default() : void
    {
        Cache::use($this->defaultCache);

        $result = Cache::get('non_existent', 'default');
        $this->assertSame('default', $result);
    }

    public function test_static_cache_can_be_swapped() : void
    {
        $mockCache1 = $this->createMock(CacheContract::class);
        $mockCache1->method('get')->with('key')->willReturn('value1');

        $mockCache2 = $this->createMock(CacheContract::class);
        $mockCache2->method('get')->with('key')->willReturn('value2');

        Cache::use($mockCache1);
        $this->assertSame('value1', Cache::get('key'));

        Cache::use($mockCache2);
        $this->assertSame('value2', Cache::get('key'));
    }

    public function test_cache_reset_clears_static_instance() : void
    {
        Cache::use($this->defaultCache);

        Cache::reset();

        $this->expectException(CacheNotConfigured::class);
        Cache::get('key');
    }

    protected function setUp() : void
    {
        Cache::reset();
        CompiledCache::reset();

        $this->clock        = new FrozenClock();
        $this->defaultStore = new InMemoryCacheStore(clock: $this->clock);
        $this->apiStore     = new InMemoryCacheStore(clock: $this->clock);

        $this->defaultCache = new AvaxCache(
            store: $this->defaultStore,
            clock: $this->clock
        );

        $this->apiCache = new AvaxCache(
            store: $this->apiStore,
            clock: $this->clock
        );
    }

    protected function tearDown() : void
    {
        Cache::reset();
        CompiledCache::reset();
    }
}