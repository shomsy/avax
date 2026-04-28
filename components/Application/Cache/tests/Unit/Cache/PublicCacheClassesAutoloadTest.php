<?php

declare(strict_types=1);

namespace Avax\Cache\Tests\Unit\Cache;

use Avax\Cache\CompiledCache;
use Avax\Cache\System\AvaxCache;
use Avax\Cache\System\Cache;
use Avax\Cache\System\CacheContract;
use Avax\Cache\System\PublicSurface\CacheFacade;
use Avax\Cache\System\PublicSurface\CacheRegistry;
use Avax\Cache\System\PublicSurface\CompiledCacheTarget;
use Avax\Cache\System\PublicSurface\ReadFromCache;
use Avax\Cache\System\PublicSurface\RuntimeCacheTarget;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class PublicCacheClassesAutoloadTest extends TestCase
{
    public function test_public_cache_classes_are_autoloadable() : void
    {
        self::assertTrue(condition: class_exists(\Avax\Cache\Cache::class), message: 'Cache facade not autoloadable');
        self::assertTrue(condition: class_exists(CompiledCache::class), message: 'CompiledCache facade not autoloadable');
        self::assertTrue(condition: interface_exists(CacheContract::class), message: 'CacheContract not autoloadable');
        self::assertTrue(condition: class_exists(AvaxCache::class), message: 'AvaxCache not autoloadable');
    }

    public function test_public_surface_classes_are_autoloadable() : void
    {
        self::assertTrue(condition: class_exists(CacheFacade::class));
        self::assertTrue(condition: class_exists(CacheRegistry::class));
        self::assertTrue(condition: class_exists(RuntimeCacheTarget::class));
        self::assertTrue(condition: class_exists(CompiledCacheTarget::class));
        self::assertTrue(condition: class_exists(ReadFromCache::class));
    }

    public function test_no_system_cache_facade_exists() : void
    {
        $systemCacheExists = class_exists(Cache::class);
        self::assertFalse(condition: $systemCacheExists, message: 'System\Cache class should not exist - use top-level Cache facade');
    }

    public function test_cache_contract_defines_correct_interface() : void
    {
        $reflection = new ReflectionClass(objectOrClass: CacheContract::class);
        self::assertTrue(condition: $reflection->isInterface(), message: 'CacheContract should be an interface');
        self::assertSame(expected: 'CacheContract', actual: $reflection->getShortName());
    }

    public function test_avax_cache_implements_cache_contract() : void
    {
        $avaxCache  = new ReflectionClass(objectOrClass: AvaxCache::class);
        $interfaces = $avaxCache->getInterfaceNames();

        self::assertContains(needle: CacheContract::class, haystack: $interfaces, message: 'AvaxCache should implement CacheContract');
    }
}