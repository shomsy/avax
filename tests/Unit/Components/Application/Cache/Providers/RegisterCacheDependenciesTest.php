<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Cache\Providers;

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

        $this->expectException(CacheNotConfigured::class);
        $this->expectExceptionMessage('Named store requires RegisterCacheDependencies');

        Cache::store(name: 'api');
    }

    public function test_cache_store_returns_default() : void
    {
        Cache::use(cache: $this->cacheContract);

        $store = Cache::store();

        $this->assertSame($this->cacheContract, $store);
    }

    public function test_cache_use_sets_default() : void
    {
        Cache::use(cache: $this->cacheContract);

        $result = Cache::get(default: 'default', key: 'non_existent');
        $this->assertSame('default', $result);
    }

    public function test_static_cache_can_be_swapped() : void
    {
        $mockCache1 = $this->createMock(CacheContract::class);
        $mockCache1->method('get')->with('key')->willReturn(value: 'value1');

        $mockCache2 = $this->createMock(CacheContract::class);
        $mockCache2->method('get')->with('key')->willReturn(value: 'value2');

        Cache::use(cache: $mockCache1);
        $this->assertSame('value1', Cache::get(key: 'key'));

        Cache::use(cache: $mockCache2);
        $this->assertSame('value2', Cache::get(key: 'key'));
    }

    public function test_cache_reset_clears_static_instance() : void
    {
        Cache::use(cache: $this->cacheContract);

        Cache::reset();

        $this->expectException(CacheNotConfigured::class);
        Cache::get(key: 'key');
    }

    #[Override]
    protected function setUp() : void
    {
        Cache::reset();
        CompiledCache::reset();

        $this->frozenClock        = new FrozenClock();
        $this->inMemoryCacheStore = new InMemoryCacheStore(clock: $this->frozenClock);

        $this->cacheContract = new AvaxCache(
            clock     : $this->frozenClock,
            cacheStore: $this->inMemoryCacheStore,
        );
    }

    #[Override]
    protected function tearDown() : void
    {
        Cache::reset();
        CompiledCache::reset();
    }
}
