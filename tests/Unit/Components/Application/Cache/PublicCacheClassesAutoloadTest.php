<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Cache;

use Avax\Components\Application\Cache\System\PublicSurface\AvaxCache;
use Avax\Components\Application\Cache\System\PublicSurface\CacheContract;
use Avax\Components\Application\Cache\System\PublicSurface\Cache;
use Avax\Components\Application\Cache\System\PublicSurface\CacheReadTarget;
use Avax\Components\Application\Cache\System\PublicSurface\CompiledCache;
use Avax\Components\Application\Cache\System\PublicSurface\Gateways\CacheGateway;
use Avax\Components\Application\Cache\System\PublicSurface\Gateways\CacheRegistry;
use Avax\Components\Application\Cache\System\PublicSurface\Read\CompiledCacheTarget;
use Avax\Components\Application\Cache\System\PublicSurface\Read\ReadFromCache;
use Avax\Components\Application\Cache\System\PublicSurface\Read\RuntimeCacheTarget;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class PublicCacheClassesAutoloadTest extends TestCase
{
    public function test_public_cache_classes_are_autoloadable() : void
    {
        self::assertTrue(class_exists(AvaxCache::class), 'AvaxCache not autoloadable');
        self::assertTrue(class_exists(CompiledCache::class), 'CompiledCache facade not autoloadable');
        self::assertTrue(interface_exists(CacheContract::class), 'CacheContract not autoloadable');
        self::assertTrue(class_exists(AvaxCache::class), 'AvaxCache not autoloadable');
    }

    public function test_public_surface_classes_are_autoloadable() : void
    {
        self::assertTrue(class_exists(CacheGateway::class));
        self::assertTrue(class_exists(CacheRegistry::class));
        self::assertTrue(class_exists(RuntimeCacheTarget::class));
        self::assertTrue(class_exists(CompiledCacheTarget::class));
        self::assertTrue(class_exists(ReadFromCache::class));
    }

    public function test_avax_cache_implements_cache_contract() : void
    {
        $reflectionClass = new ReflectionClass(objectOrClass: AvaxCache::class);
        $interfaces      = $reflectionClass->getInterfaceNames();

        self::assertContains(CacheContract::class, $interfaces, 'AvaxCache should implement CacheContract');
    }

    public function test_cache_contract_defines_correct_interface() : void
    {
        $reflectionClass = new ReflectionClass(objectOrClass: CacheContract::class);
        self::assertTrue($reflectionClass->isInterface(), 'CacheContract should be an interface');
        self::assertSame('CacheContract', $reflectionClass->getShortName());
    }
}
