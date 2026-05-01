<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Cache\Unit\Cache\Providers;

use Avax\Components\Application\Cache\Cache;
use Avax\Components\Application\Cache\CompiledCache;
use Avax\Components\Application\Cache\System\AvaxCache;
use Avax\Components\Application\Cache\System\CacheContract;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\InMemoryCacheStore;
use Avax\Components\Application\Cache\System\Foundation\Time\FrozenClock;
use PHPUnit\Framework\TestCase;

final class RegisterCacheDependenciesTest extends TestCase
{
    private FrozenClock   $clock;
    private InMemoryCacheStore $defaultStore;
    private InMemoryCacheStore $apiStore;
    private CacheContract $defaultCache;
    private CacheContract $apiCache;

    public function test_cache_store_with_name_requires_provider() : void
    {
        Cache::reset();

        $this->expectException(exception: CacheNotConfigured::class);
        $this->expectExceptionMessage(message: 'Named store requires RegisterCacheDependencies');

        Cache::store(name: 'api');
    }

    public function test_cache_store_returns_default() : void
    {
        Cache::use(cache: $this->defaultCache);

        $store = Cache::store();

        $this->assertSame(expected: $this->defaultCache, actual: $store);
    }

    public function test_cache_use_sets_default() : void
    {
        Cache::use(cache: $this->defaultCache);

        $result = Cache::get(key: 'non_existent', default: 'default');
        $this->assertSame(expected: 'default', actual: $result);
    }

    public function test_static_cache_can_be_swapped() : void
    {
        $mockCache1 = $this->createMock(CacheContract::class);
        $mockCache1->method('get')->with('key')->willReturn(value: 'value1');

        $mockCache2 = $this->createMock(CacheContract::class);
        $mockCache2->method('get')->with('key')->willReturn(value: 'value2');

        Cache::use(cache: $mockCache1);
        $this->assertSame(expected: 'value1', actual: Cache::get(key: 'key'));

        Cache::use(cache: $mockCache2);
        $this->assertSame(expected: 'value2', actual: Cache::get(key: 'key'));
    }

    public function test_cache_reset_clears_static_instance() : void
    {
        Cache::use(cache: $this->defaultCache);

        Cache::reset();

        $this->expectException(exception: CacheNotConfigured::class);
        Cache::get(key: 'key');
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
            clock: $this->clock,
        );

        $this->apiCache = new AvaxCache(
            store: $this->apiStore,
            clock: $this->clock,
        );
    }

    protected function tearDown() : void
    {
        Cache::reset();
        CompiledCache::reset();
    }
}
