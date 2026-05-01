<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System;

use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheArtifact;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheContract;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheSources;
use Avax\Components\Application\Cache\System\Configuration\CompiledCacheConfiguration\BuildCompiledCache;
use Avax\Components\Application\Cache\System\Configuration\CompiledCacheConfiguration\CompiledCacheConfiguration;

final class CompiledCache
{
    private static CompiledCacheContract|null $compiledCacheContract = null;

    private static string|null $defaultDirectory = null;

    public static function read(string $name, callable $build, CompiledCacheSources $sources) : mixed
    {
        return self::instance()->read(name: $name, build: $build, sources: $sources);
    }

    private static function instance() : CompiledCacheContract
    {
        if (self::$compiledCacheContract === null) {
            $directory = self::$defaultDirectory ?? sys_get_temp_dir() . '/compiled_cache';

            $config  = CompiledCacheConfiguration::inDirectory($directory);
            $buildCompiledCache = new BuildCompiledCache();

            self::$compiledCacheContract = $buildCompiledCache->fromConfiguration(configuration: $config);
        }

        return self::$compiledCacheContract;
    }

    public static function compile(string $name, callable $build, CompiledCacheSources $sources) : CompiledCacheArtifact
    {
        return self::instance()->compile(name: $name, build: $build, sources: $sources);
    }

    public static function clear(string $name) : void
    {
        self::instance()->clear(name: $name);
    }

    public static function clearAll() : void
    {
        self::instance()->clearAll();
    }

    public static function use(CompiledCacheContract $compiledCacheContract) : void
    {
        self::$compiledCacheContract = $compiledCacheContract;
    }

    public static function configure(string $directory) : void
    {
        self::$defaultDirectory = $directory;
        self::$compiledCacheContract = null;
    }

    public static function reset() : void
    {
        self::$compiledCacheContract = null;
        self::$defaultDirectory = null;
    }
}
