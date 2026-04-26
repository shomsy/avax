<?php

declare(strict_types=1);

namespace Avax\Cache\Tests\Unit\Cache\PublicSurface;

use Avax\Cache\Cache;
use Avax\Cache\CompiledCache;
use Avax\Cache\System\AvaxCache;
use Avax\Cache\System\CacheContract;
use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCacheNotConfigured;
use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCacheSources;
use Avax\Cache\System\Capabilities\StoreCachedValues\InMemoryCacheStore;
use Avax\Cache\System\Foundation\Time\FrozenClock;
use Avax\Cache\System\PublicSurface\CacheNotConfigured;
use Avax\Cache\System\PublicSurface\CacheReadKind;
use Avax\Cache\System\PublicSurface\CacheReadTarget;
use Avax\Cache\System\PublicSurface\CacheReadTargetWasNotSupported;
use Avax\Cache\System\PublicSurface\CompiledCacheTarget;
use Avax\Cache\System\PublicSurface\RuntimeCacheTarget;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionUnionType;

final class CacheReadRoutingTest extends TestCase
{
    private FrozenClock        $clock;
    private InMemoryCacheStore $store;
    private CacheContract      $cache;

    public function test_read_method_exists() : void
    {
        $this->assertTrue(method_exists(Cache::class, 'read'));
    }

    public function test_read_routes_string_to_runtime_cache() : void
    {
        $this->cache->set('test_key', 'test_value');

        $result = Cache::read('test_key');

        $this->assertSame('test_value', $result);
    }

    public function test_read_routes_string_with_default() : void
    {
        $result = Cache::read('nonexistent', 'default_value');

        $this->assertSame('default_value', $result);
    }

    public function test_read_routes_runtime_cache_target_with_own_default() : void
    {
        $this->cache->set('runtime_key', 'runtime_value');

        $target = RuntimeCacheTarget::key('runtime_key', $default = 'target_default');
        $result = Cache::read($target);

        $this->assertSame('runtime_value', $result);
    }

    public function test_read_uses_target_default_when_key_missing() : void
    {
        $target = RuntimeCacheTarget::key('missing_key', $default = 'target_default');
        $result = Cache::read($target);

        $this->assertSame('target_default', $result);
    }

    public function test_read_signature_accepts_cache_read_target() : void
    {
        $param = (new ReflectionMethod(Cache::class, 'read'))->getParameters()[0];
        $type  = $param->getType();

        $this->assertInstanceOf(ReflectionUnionType::class, $type);

        $types     = $type->getTypes();
        $typeNames = array_map(fn ($t) => $t->getName(), $types);

        $this->assertContains('Avax\Cache\System\PublicSurface\CacheReadTarget', $typeNames);
        $this->assertContains('string', $typeNames);
    }

    public function test_compiled_cache_read_has_correct_signature() : void
    {
        $params = (new ReflectionMethod(CompiledCache::class, 'read'))->getParameters();

        $this->assertCount(3, $params);
        $this->assertSame('name', $params[0]->getName());
        $this->assertSame('build', $params[1]->getName());
        $this->assertSame('sources', $params[2]->getName());
    }

    public function test_compiled_cache_compile_has_correct_signature() : void
    {
        $params = (new ReflectionMethod(CompiledCache::class, 'compile'))->getParameters();

        $this->assertCount(3, $params);
        $this->assertSame('name', $params[0]->getName());
        $this->assertSame('build', $params[1]->getName());
        $this->assertSame('sources', $params[2]->getName());
    }

    public function test_compiled_cache_throws_when_not_configured() : void
    {
        $this->expectException(\Avax\Cache\System\PublicSurface\CompiledCacheNotConfigured::class);

        CompiledCache::read('name', fn () => [], new CompiledCacheSources());
    }

    public function test_compiled_cache_compile_throws_when_not_configured() : void
    {
        $this->expectException(\Avax\Cache\System\PublicSurface\CompiledCacheNotConfigured::class);

        CompiledCache::compile('name', fn () => [], new CompiledCacheSources());
    }

    public function test_cache_read_throws_for_compiled_target_without_provider() : void
    {
        $target = CompiledCacheTarget::artifact(
            name   : 'routes',
            builder: fn () => [],
            sources: new CompiledCacheSources()
        );

        $this->expectException(CacheNotConfigured::class);
        Cache::read($target);
    }

    public function test_cache_facade_resolves_when_container_exists() : void
    {
        Cache::reset();

        $this->expectException(CacheNotConfigured::class);
        Cache::get('test');
    }

    public function test_cache_use_overrides_container() : void
    {
        $mockCache = $this->createMock(CacheContract::class);
        $mockCache->method('get')->with('key')->willReturn('mocked');

        Cache::use($mockCache);

        $this->assertSame('mocked', Cache::get('key'));
    }

    public function test_cache_reset_clears_instance() : void
    {
        Cache::use($this->cache);
        Cache::get('key');

        Cache::reset();

        $this->expectException(CacheNotConfigured::class);
        Cache::get('key');
    }

    public function test_store_returns_default_cache() : void
    {
        $store = Cache::store();

        $this->assertNotNull($store);
    }

    public function test_store_with_name_throws_without_provider() : void
    {
        $this->expectException(CacheNotConfigured::class);
        $this->expectExceptionMessage('Named store requires CacheServiceProvider');

        Cache::store('api');
    }

    public function test_read_throws_for_unknown_target_without_provider() : void
    {
        $this->expectException(CacheReadTargetWasNotSupported::class);

        Cache::read(new class implements CacheReadTarget {
            public function kind() : CacheReadKind
            {
                return CacheReadKind::RUNTIME;
            }
        });
    }

    public function test_read_routes_to_named_store_without_provider() : void
    {
        $this->expectException(CacheNotConfigured::class);
        $this->expectExceptionMessage('Named store requires CacheServiceProvider');

        $target = RuntimeCacheTarget::key('key', null, 'redis');
        Cache::read($target);
    }

    protected function setUp() : void
    {
        Cache::reset();
        CompiledCache::reset();

        $this->clock = new FrozenClock();
        $this->store = new InMemoryCacheStore(clock: $this->clock);
        $this->cache = new AvaxCache(store: $this->store, clock: $this->clock);

        Cache::use($this->cache);
    }

    protected function tearDown() : void
    {
        Cache::reset();
        CompiledCache::reset();
    }
}