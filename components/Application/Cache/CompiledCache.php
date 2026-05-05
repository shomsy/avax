<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache;

use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheArtifact;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheContract;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheSources;
use Avax\Components\Application\Cache\System\CompiledCache as SystemCompiledCache;

/**
 * Stable public facade for compiled cache artifacts.
 */
final class CompiledCache
{
    public static function read(string $name, callable $build, CompiledCacheSources $sources): mixed
    {
        return SystemCompiledCache::read(name: $name, build: $build, compiledCacheSources: $sources);
    }

    public static function compile(string $name, callable $build, CompiledCacheSources $sources): CompiledCacheArtifact
    {
        return SystemCompiledCache::compile(name: $name, build: $build, compiledCacheSources: $sources);
    }

    public static function clear(string $name): void
    {
        SystemCompiledCache::clear(name: $name);
    }

    public static function clearAll(): void
    {
        SystemCompiledCache::clearAll();
    }

    public static function use(CompiledCacheContract $compiledCacheContract): void
    {
        SystemCompiledCache::use(compiledCacheContract: $compiledCacheContract);
    }

    public static function configure(string $directory): void
    {
        SystemCompiledCache::configure(directory: $directory);
    }

    public static function reset(): void
    {
        SystemCompiledCache::reset();
    }
}
