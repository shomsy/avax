<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\PublicSurface;

use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheArtifact;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheContract;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheSources;
use Avax\Components\Application\Cache\System\Configuration\CompiledCacheConfiguration\Builders\BuildCompiledCache;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;
use Avax\Components\Application\Cache\System\PublicSurface\Exception\NotConfigured;
use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;

/**
 * Stable public facade for compiled cache artifacts.
 */
final class CompiledCache
{
    private static ?CompiledCacheContract $compiledCacheContract = null;

    public static function read(string $name, callable $build, CompiledCacheSources $sources): mixed
    {
        return self::instance()->read(name: $name, build: $build, sources: $sources);
    }

    private static function instance(): CompiledCacheContract
    {
        if (! self::$compiledCacheContract instanceof CompiledCacheContract) {
            throw new NotConfigured(message: 'No compiled cache configured');
        }

        return self::$compiledCacheContract;
    }

    public static function compile(string $name, callable $build, CompiledCacheSources $sources): CompiledCacheArtifact
    {
        return self::instance()->compile(name: $name, build: $build, sources: $sources);
    }

    public static function clear(string $name): void
    {
        self::instance()->clear(name: $name);
    }

    public static function clearAll(): void
    {
        self::instance()->clearAll();
    }

    public static function use(CompiledCacheContract $compiledCacheContract): void
    {
        self::$compiledCacheContract = $compiledCacheContract;
    }

    public static function configure(string $directory): void
    {
        self::$compiledCacheContract = (new BuildCompiledCache(
            clock     : new SystemClock(),
            filesystem: new Filesystem(),
        ))->inDirectory(directory: $directory);
    }

    public static function reset(): void
    {
        self::$compiledCacheContract = null;
    }
}
