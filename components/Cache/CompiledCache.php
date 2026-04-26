<?php

declare(strict_types=1);

namespace Avax\Cache;

use Avax\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheArtifact;
use Avax\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheContract;
use Avax\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheSources;
use Avax\Cache\System\PublicSurface\Exception\CompiledNotConfigured;
use Throwable;

final class CompiledCache
{
    private static CompiledCacheContract|null $instance = null;

    public static function use(CompiledCacheContract $compiledCache) : void
    {
        self::$instance = $compiledCache;
    }

    public static function reset() : void
    {
        self::$instance = null;
    }

    /**
     * @template T
     *
     * @param callable(): T $build
     */
    public static function read(
        string               $name,
        callable             $build,
        CompiledCacheSources $sources
    ) : mixed
    {
        return self::resolve()->read(name: $name, build: $build, sources: $sources);
    }

    private static function resolve() : CompiledCacheContract
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        if (function_exists('app')) {
            try {
                $app = app();
                if ($app->has(CompiledCacheContract::class)) {
                    return $app(CompiledCacheContract::class);
                }
            } catch (Throwable) {
            }
        }

        throw new CompiledNotConfigured();
    }

    public static function compile(
        string               $name,
        callable             $build,
        CompiledCacheSources $sources
    ) : CompiledCacheArtifact
    {
        return self::resolve()->compile(name: $name, build: $build, sources: $sources);
    }

    public static function clear(string $name) : void
    {
        self::resolve()->clear(name: $name);
    }

    public static function clearAll() : void
    {
        self::resolve()->clearAll();
    }
}