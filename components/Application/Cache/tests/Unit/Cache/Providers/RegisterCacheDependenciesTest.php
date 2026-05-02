<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\Tests\Unit\Cache\Providers;

use Avax\Components\Application\Cache\Cache;
use Avax\Components\Application\Cache\CompiledCache;
use Avax\Components\Application\Cache\System\AvaxCache;
use Avax\Components\Application\Cache\System\CacheContract;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\InMemoryCacheStore;
use Avax\Components\Application\Cache\System\Foundation\Time\FrozenClock;
use Avax\Components\Application\Cache\System\PublicSurface\Exception\NotConfigured as CacheNotConfigured;
use Override;
use PHPUnit\Framework\TestCase;

final class RegisterCacheDependenciesTest extends TestCase
{
    private FrozenClock $frozenClock;

    private InMemoryCacheStore $inMemoryCacheStore;

    private CacheContract $cacheContract;

    public function test_cache_store_with_name_requires_provider() : void
    {
        Cache::reset();

        $this->expectException(exception: CacheNotConfigured::class);
        $this->expectExceptionMessage(message: 'Named store requires RegisterCacheDependencies');

        Cache::store(name: 'api');
    }

    public function test_cache_store_returns_default() : void
    {
        Cache::use(cache: $this->cacheContract);

        $store = Cache::store();

        $this->assertSame(expected: $this->cacheContract, actual: $store);
    }

    public function test_cache_use_sets_default() : void
    {
        Cache::use(cache: $this->cacheContract);

        $result = Cache::get(default: 'default', cacheKey: 'non_existent');
        $this->assertSame(expected: 'default', actual: $result);
    }

    public function test_static_cache_can_be_swapped() : void
    {
        $mockCache1 = $this->createMock(CacheContract::class);
        $mockCache1->method('get')->with('key')->willReturn(value: 'value1');

        $mockCache2 = $this->createMock(CacheContract::class);
        $mockCache2->method('get')->with('key')->willReturn(value: 'value2');

        Cache::use(cache: $mockCache1);
        $this->assertSame(expected: 'value1', actual: Cache::get(cacheKey: 'key'));

        Cache::use(cache: $mockCache2);
        $this->assertSame(expected: 'value2', actual: Cache::get(cacheKey: 'key'));
    }

    public function test_cache_reset_clears_static_instance() : void
    {
        Cache::use(cache: $this->cacheContract);

        Cache::reset();

        $this->expectException(exception: CacheNotConfigured::class);
        Cache::get(cacheKey: 'key');
    }

    #[Override]
    protected function setUp() : void
    {
        Cache::reset();
        CompiledCache::reset();

        $this->frozenClock = new FrozenClock();
        $this->inMemoryCacheStore = new InMemoryCacheStore(clock: $this->frozenClock);

        $this->cacheContract = new AvaxCache(
            clock: $this->frozenClock,
            store: $this->inMemoryCacheStore,
        );
    }

    #[Override]
    protected function tearDown() : void
    {
        Cache::reset();
        CompiledCache::reset();
    }
}
