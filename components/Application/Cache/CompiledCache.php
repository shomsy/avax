<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache;

use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheArtifact;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheContract;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheSources;
use Avax\Components\Application\Cache\System\PublicSurface\Exception\NotConfigured;

/**
 * Stable static entrypoint for compiled cache operations.
 */
final class CompiledCache
{
    private static CompiledCacheContract|null $compiledCacheContract = null;

    public static function use(CompiledCacheContract $compiledCacheContract) : void
    {
        self::$compiledCacheContract = $compiledCacheContract;
    }

    public static function reset() : void
    {
        self::$compiledCacheContract = null;
    }

    public static function read(string $name, callable $build, CompiledCacheSources $compiledCacheSources) : mixed
    {
        return self::instance()->read(name: $name, build: $build, sources: $compiledCacheSources);
    }

    private static function instance() : CompiledCacheContract
    {
        if (! self::$compiledCacheContract instanceof CompiledCacheContract) {
            throw new NotConfigured(message: 'No compiled cache configured');
        }

        return self::$compiledCacheContract;
    }

    public static function compile(string $name, callable $build, CompiledCacheSources $compiledCacheSources) : CompiledCacheArtifact
    {
        return self::instance()->compile(name: $name, build: $build, sources: $compiledCacheSources);
    }
}
