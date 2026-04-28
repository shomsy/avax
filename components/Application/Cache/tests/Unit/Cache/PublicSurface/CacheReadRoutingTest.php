<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\tests\Unit\Cache\PublicSurface;

use Avax\Components\Application\Cache\Cache;
use Avax\Components\Application\Cache\CompiledCache;
use Avax\Components\Application\Cache\System\AvaxCache;
use Avax\Components\Application\Cache\System\CacheContract;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheSources;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\InMemoryCacheStore;
use Avax\Components\Application\Cache\System\Foundation\Time\FrozenClock;
use Avax\Components\Application\Cache\System\PublicSurface\Exception\NotConfigured as CacheNotConfigured;
use Avax\Components\Application\Cache\System\PublicSurface\Exception\NotConfigured as CompiledNotConfigured;
use Avax\Components\Application\Cache\System\PublicSurface\Exception\UnsupportedTarget as CacheReadTargetWasNotSupported;
use Avax\Components\Application\Cache\System\PublicSurface\Read\CacheReadKind;
use Avax\Components\Application\Cache\System\PublicSurface\Read\CacheReadTarget;
use Avax\Components\Application\Cache\System\PublicSurface\Read\CompiledCacheTarget;
use Avax\Components\Application\Cache\System\PublicSurface\Read\RuntimeCacheTarget;
use Avax\Tests\TestCase;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionMethod;
use ReflectionUnionType;

final class CacheReadRoutingTest extends TestCase
{
    private FrozenClock        $clock;
    private InMemoryCacheStore $store;
    private CacheContract      $cache;

    public function test_read_method_exists() : void
    {
        $this->assertTrue(condition: method_exists(Cache::class, 'read'));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function test_read_routes_string_to_runtime_cache() : void
    {
        $this->cache->set(key: 'test_key', value: 'test_value');

        $result = Cache::read(target: 'test_key');

        $this->assertSame(expected: 'test_value', actual: $result);
    }

    public function test_read_routes_string_with_default() : void
    {
        $result = Cache::read(target: 'nonexistent', default: 'default_value');

        $this->assertSame(expected: 'default_value', actual: $result);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function test_read_routes_runtime_cache_target_with_own_default() : void
    {
        $this->cache->set(key: 'runtime_key', value: 'runtime_value');

        $target = RuntimeCacheTarget::key('runtime_key', $default = 'target_default');
        $result = Cache::read(target: $target);

        $this->assertSame(expected: 'runtime_value', actual: $result);
    }

    public function test_read_uses_target_default_when_key_missing() : void
    {
        $target = RuntimeCacheTarget::key('missing_key', $default = 'target_default');
        $result = Cache::read(target: $target);

        $this->assertSame(expected: 'target_default', actual: $result);
    }

    public function test_read_signature_accepts_cache_read_target() : void
    {
        $param = (new ReflectionMethod(objectOrMethod: Cache::class, method: 'read'))->getParameters()[0];
        $type  = $param->getType();

        $this->assertInstanceOf(expected: ReflectionUnionType::class, actual: $type);

        $types     = $type->getTypes();
        $typeNames = array_map(fn ($t) => $t->getName(), $types);

        $this->assertContains(needle: 'Avax\Cache\System\PublicSurface\CacheReadTarget', haystack: $typeNames);
        $this->assertContains(needle: 'string', haystack: $typeNames);
    }

    public function test_compiled_cache_read_has_correct_signature() : void
    {
        $params = (new ReflectionMethod(objectOrMethod: CompiledCache::class, method: 'read'))->getParameters();

        $this->assertCount(expectedCount: 3, haystack: $params);
        $this->assertSame(expected: 'name', actual: $params[0]->getName());
        $this->assertSame(expected: 'build', actual: $params[1]->getName());
        $this->assertSame(expected: 'sources', actual: $params[2]->getName());
    }

    public function test_compiled_cache_compile_has_correct_signature() : void
    {
        $params = (new ReflectionMethod(objectOrMethod: CompiledCache::class, method: 'compile'))->getParameters();

        $this->assertCount(expectedCount: 3, haystack: $params);
        $this->assertSame(expected: 'name', actual: $params[0]->getName());
        $this->assertSame(expected: 'build', actual: $params[1]->getName());
        $this->assertSame(expected: 'sources', actual: $params[2]->getName());
    }

    public function test_compiled_cache_throws_when_not_configured() : void
    {
        $this->expectException(exception: CompiledNotConfigured::class);

        CompiledCache::read(name: 'name', build: static fn () => [], sources: new CompiledCacheSources());
    }

    public function test_compiled_cache_compile_throws_when_not_configured() : void
    {
        $this->expectException(exception: CompiledNotConfigured::class);

        CompiledCache::compile(name: 'name', build: static fn () => [], sources: new CompiledCacheSources());
    }

    public function test_cache_read_throws_for_compiled_target_without_provider() : void
    {
        $target = CompiledCacheTarget::artifact(
            name   : 'routes',
            builder: static fn () => [],
            sources: new CompiledCacheSources()
        );

        $this->expectException(exception: CacheNotConfigured::class);
        Cache::read(target: $target);
    }

    public function test_cache_facade_resolves_when_container_exists() : void
    {
        Cache::reset();

        $this->expectException(exception: CacheNotConfigured::class);
        Cache::get(key: 'test');
    }

    public function test_cache_use_overrides_container() : void
    {
        $mockCache = $this->createMock(CacheContract::class);
        $mockCache->method('get')->with('key')->willReturn(value: 'mocked');

        Cache::use(cache: $mockCache);

        $this->assertSame(expected: 'mocked', actual: Cache::get(key: 'key'));
    }

    public function test_cache_reset_clears_instance() : void
    {
        Cache::use(cache: $this->cache);
        Cache::get(key: 'key');

        Cache::reset();

        $this->expectException(exception: CacheNotConfigured::class);
        Cache::get(key: 'key');
    }

    public function test_store_returns_default_cache() : void
    {
        $store = Cache::store();

        $this->assertNotNull(actual: $store);
    }

    public function test_store_with_name_throws_without_provider() : void
    {
        $this->expectException(exception: CacheNotConfigured::class);
        $this->expectExceptionMessage(message: 'Named store requires CacheServiceProvider');

        Cache::store(name: 'api');
    }

    public function test_read_throws_for_unknown_target_without_provider() : void
    {
        $this->expectException(exception: CacheReadTargetWasNotSupported::class);

        Cache::read(target: new class implements CacheReadTarget {
            public function kind() : CacheReadKind
            {
                return CacheReadKind::RUNTIME;
            }
        });
    }

    public function test_read_routes_to_named_store_without_provider() : void
    {
        $this->expectException(exception: CacheNotConfigured::class);
        $this->expectExceptionMessage(message: 'Named store requires CacheServiceProvider');

        $target = RuntimeCacheTarget::key('key', null, 'redis');
        Cache::read(target: $target);
    }

    protected function setUp() : void
    {
        parent::setUp();
        Cache::reset();
        CompiledCache::reset();

        $this->clock = new FrozenClock();
        $this->store = new InMemoryCacheStore(clock: $this->clock);
        $this->cache = new AvaxCache(store: $this->store, clock: $this->clock);

        Cache::use(cache: $this->cache);
    }

    protected function tearDown() : void
    {
        parent::tearDown();
        Cache::reset();
        CompiledCache::reset();
    }
}