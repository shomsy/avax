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
        self::assertTrue(class_exists(\Avax\Cache\Cache::class), 'Cache facade not autoloadable');
        self::assertTrue(class_exists(CompiledCache::class), 'CompiledCache facade not autoloadable');
        self::assertTrue(interface_exists(CacheContract::class), 'CacheContract not autoloadable');
        self::assertTrue(class_exists(AvaxCache::class), 'AvaxCache not autoloadable');
    }

    public function test_public_surface_classes_are_autoloadable() : void
    {
        self::assertTrue(class_exists(CacheFacade::class));
        self::assertTrue(class_exists(CacheRegistry::class));
        self::assertTrue(class_exists(RuntimeCacheTarget::class));
        self::assertTrue(class_exists(CompiledCacheTarget::class));
        self::assertTrue(class_exists(ReadFromCache::class));
    }

    public function test_no_system_cache_facade_exists() : void
    {
        $systemCacheExists = class_exists(Cache::class);
        self::assertFalse($systemCacheExists, 'System\Cache class should not exist - use top-level Cache facade');
    }

    public function test_cache_contract_defines_correct_interface() : void
    {
        $reflection = new ReflectionClass(CacheContract::class);
        self::assertTrue($reflection->isInterface(), 'CacheContract should be an interface');
        self::assertSame('CacheContract', $reflection->getShortName());
    }

    public function test_avax_cache_implements_cache_contract() : void
    {
        $avaxCache  = new ReflectionClass(AvaxCache::class);
        $interfaces = $avaxCache->getInterfaceNames();

        self::assertContains(CacheContract::class, $interfaces, 'AvaxCache should implement CacheContract');
    }
}