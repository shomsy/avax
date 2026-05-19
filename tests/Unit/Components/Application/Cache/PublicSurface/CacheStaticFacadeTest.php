<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Cache\PublicSurface;

use Avax\Components\Application\Cache\System\PublicSurface\CacheContract;
use Avax\Components\Application\Cache\System\PublicSurface\Cache;
use Avax\Components\Application\Cache\System\PublicSurface\Exception\NotConfigured as CacheNotConfigured;
use Avax\Tests\TestCase;
use Override;

final class CacheStaticFacadeTest extends TestCase
{
    public function test_it_throws_when_no_cache_configured() : void
    {
        $this->expectException(CacheNotConfigured::class);

        Cache::get('key');
    }

    public function test_it_reads_value_through_static_facade() : void
    {
        $mockCache = $this->createMock(CacheContract::class);
        $mockCache->method('get')->with('key', 'default')->willReturn('value');

        Cache::use(cache: $mockCache);

        $result = Cache::get(key: 'key', default: 'default');

        $this->assertSame('value', $result);
    }

    public function test_it_writes_value_through_static_facade() : void
    {
        $mockCache = $this->createMock(CacheContract::class);
        $mockCache->expects($this->once())->method('set')->with('key', 'value', null)->willReturn(true);

        Cache::use(cache: $mockCache);

        $result = Cache::set(key: 'key', value: 'value');

        $this->assertTrue($result);
    }

    public function test_it_put_is_alias_for_set() : void
    {
        $mockCache = $this->createMock(CacheContract::class);
        $mockCache->expects($this->once())->method('set')->with('key', 'value', 3600)->willReturn(true);

        Cache::use(cache: $mockCache);

        $result = Cache::put(key: 'key', value: 'value', ttl: 3600);

        $this->assertTrue($result);
    }

    public function test_it_remembers_value_through_static_facade() : void
    {
        $mockCache = $this->createMock(CacheContract::class);
        $mockCache->expects($this->once())->method('remember')->with('key', 3600, $this->isType('callable'))->willReturn('loaded');

        Cache::use(cache: $mockCache);

        $result = Cache::remember(key: 'key', ttl: 3600, loader: static fn () : string => 'loaded');

        $this->assertSame('loaded', $result);
    }

    public function test_it_forgets_value_through_static_facade() : void
    {
        $mockCache = $this->createMock(CacheContract::class);
        $mockCache->expects($this->once())->method('delete')->with('key')->willReturn(true);

        Cache::use(cache: $mockCache);

        $result = Cache::forget(key: 'key');

        $this->assertTrue($result);
    }

    public function test_it_clears_through_static_facade() : void
    {
        $mockCache = $this->createMock(CacheContract::class);
        $mockCache->expects($this->once())->method('clear')->willReturn(true);

        Cache::use(cache: $mockCache);

        $result = Cache::clear();

        $this->assertTrue($result);
    }

    public function test_it_resolves_default_store() : void
    {
        $mockCache = $this->createMock(CacheContract::class);

        Cache::use(cache: $mockCache);

        $store = Cache::store();

        $this->assertSame($mockCache, $store);
    }

    public function test_it_allows_cache_to_be_swapped_for_tests() : void
    {
        $cache1 = $this->createMock(CacheContract::class);
        $cache1->method('get')->with('key')->willReturn('value1');

        $cache2 = $this->createMock(CacheContract::class);
        $cache2->method('get')->with('key')->willReturn('value2');

        Cache::use(cache: $cache1);
        $this->assertSame('value1', Cache::get(key: 'key'));

        Cache::use(cache: $cache2);
        $this->assertSame('value2', Cache::get(key: 'key'));
    }

    public function test_it_resets_static_facade_state_between_tests() : void
    {
        $mockCache = $this->createMock(CacheContract::class);
        $mockCache->method('get')->with('key')->willReturn('value');

        Cache::use(cache: $mockCache);
        Cache::get(key: 'key');

        Cache::reset();

        $this->expectException(CacheNotConfigured::class);
        Cache::get(key: 'key');
    }

    #[Override]
    protected function setUp() : void
    {
        parent::setUp();
        Cache::reset();
    }

    #[Override]
    protected function tearDown() : void
    {
        parent::tearDown();
        Cache::reset();
    }
}
