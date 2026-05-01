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
    private static CompiledCacheContract|null $compiledCache = null;

    public static function use(CompiledCacheContract $compiledCache) : void
    {
        self::$compiledCache = $compiledCache;
    }

    public static function reset() : void
    {
        self::$compiledCache = null;
    }

    public static function read(string $name, callable $build, CompiledCacheSources $sources) : mixed
    {
        return self::instance()->read(name: $name, build: $build, sources: $sources);
    }

    private static function instance() : CompiledCacheContract
    {
        if (self::$compiledCache === null) {
            throw new NotConfigured(message: 'No compiled cache configured');
        }

        return self::$compiledCache;
    }

    public static function compile(string $name, callable $build, CompiledCacheSources $sources) : CompiledCacheArtifact
    {
        return self::instance()->compile(name: $name, build: $build, sources: $sources);
    }
}
