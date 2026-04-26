<?php

declare(strict_types=1);

namespace Avax\Cache\System;

use Avax\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheArtifact;
use Avax\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheContract;
use Avax\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheSources;
use Avax\Cache\System\Configuration\CompiledCacheConfiguration;
use Avax\Cache\System\Configuration\CompiledCacheConfiguration\BuildCompiledCache;

final class CompiledCache
{
    private static CompiledCacheContract|null $instance         = null;
    private static string|null                $defaultDirectory = null;

    public static function read(string $name, callable $build, CompiledCacheSources $sources) : mixed
    {
        return self::instance()->read(name: $name, build: $build, sources: $sources);
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

    public static function use(CompiledCacheContract $cache) : void
    {
        self::$instance = $cache;
    }

    public static function configure(string $directory) : void
    {
        self::$defaultDirectory = $directory;
        self::$instance         = null;
    }

    public static function reset() : void
    {
        self::$instance         = null;
        self::$defaultDirectory = null;
    }

    private static function instance() : CompiledCacheContract
    {
        if (self::$instance === null) {
            $directory = self::$defaultDirectory ?? sys_get_temp_dir() . '/compiled_cache';

            $config  = CompiledCacheConfiguration::inDirectory($directory);
            $builder = new BuildCompiledCache();

            self::$instance = $builder->fromConfiguration(configuration: $config);
        }

        return self::$instance;
    }
}