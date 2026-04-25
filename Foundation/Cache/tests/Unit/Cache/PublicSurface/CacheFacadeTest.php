<?php

declare(strict_types=1);

namespace Avax\Cache\Tests\Unit\Cache\PublicSurface;

use Avax\Cache\System\Cache;
use Avax\Cache\System\CacheContract;
use Avax\Cache\System\PublicSurface\CacheNotConfigured;
use PHPUnit\Framework\TestCase;

final class CacheFacadeTest extends TestCase
{
    protected function setUp() : void
    {
        Cache::reset();
    }

    protected function tearDown() : void
    {
        Cache::reset();
    }

    public function test_it_throws_when_no_cache_configured() : void
    {
        $this->expectException(CacheNotConfigured::class);

        Cache::get('key');
    }

    public function test_it_reads_value_through_static_facade() : void
    {
        $mockCache = $this->createMock(CacheContract::class);
        $mockCache->method('get')->with('key', 'default')->willReturn('value');

        Cache::use($mockCache);

        $result = Cache::get('key', 'default');

        $this->assertSame('value', $result);
    }

    public function test_it_writes_value_through_static_facade() : void
    {
        $mockCache = $this->createMock(CacheContract::class);
        $mockCache->expects($this->once())->method('set')->with('key', 'value', null)->willReturn(true);

        Cache::use($mockCache);

        $result = Cache::set('key', 'value');

        $this->assertTrue($result);
    }

    public function test_it_put_is_alias_for_set() : void
    {
        $mockCache = $this->createMock(CacheContract::class);
        $mockCache->expects($this->once())->method('set')->with('key', 'value', 3600)->willReturn(true);

        Cache::use($mockCache);

        $result = Cache::put('key', 'value', 3600);

        $this->assertTrue($result);
    }

    public function test_it_remembers_value_through_static_facade() : void
    {
        $mockCache = $this->createMock(CacheContract::class);
        $mockCache->expects($this->once())->method('remember')->with('key', 3600, $this->isType('callable'))->willReturn('loaded');

        Cache::use($mockCache);

        $result = Cache::remember('key', 3600, fn () => 'loaded');

        $this->assertSame('loaded', $result);
    }

    public function test_it_forgets_value_through_static_facade() : void
    {
        $mockCache = $this->createMock(CacheContract::class);
        $mockCache->expects($this->once())->method('delete')->with('key')->willReturn(true);

        Cache::use($mockCache);

        $result = Cache::forget('key');

        $this->assertTrue($result);
    }

    public function test_it_clears_through_static_facade() : void
    {
        $mockCache = $this->createMock(CacheContract::class);
        $mockCache->expects($this->once())->method('clear')->willReturn(true);

        Cache::use($mockCache);

        $result = Cache::clear();

        $this->assertTrue($result);
    }

    public function test_it_resolves_default_store() : void
    {
        $mockCache = $this->createMock(CacheContract::class);

        Cache::use($mockCache);

        $store = Cache::store();

        $this->assertSame($mockCache, $store);
    }

    public function test_it_allows_cache_to_be_swapped_for_tests() : void
    {
        $cache1 = $this->createMock(CacheContract::class);
        $cache1->method('get')->with('key')->willReturn('value1');

        $cache2 = $this->createMock(CacheContract::class);
        $cache2->method('get')->with('key')->willReturn('value2');

        Cache::use($cache1);
        $this->assertSame('value1', Cache::get('key'));

        Cache::use($cache2);
        $this->assertSame('value2', Cache::get('key'));
    }

    public function test_it_resets_static_facade_state_between_tests() : void
    {
        $mockCache = $this->createMock(CacheContract::class);
        $mockCache->method('get')->with('key')->willReturn('value');

        Cache::use($mockCache);
        Cache::get('key');

        Cache::reset();

        $this->expectException(CacheNotConfigured::class);
        Cache::get('key');
    }
}