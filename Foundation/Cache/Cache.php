<?php

declare(strict_types=1);

namespace Avax\Cache;

use Avax\Cache\System\CacheContract;
use Avax\Cache\System\PublicSurface\CacheFacade;
use Avax\Cache\System\PublicSurface\CacheNotConfigured;
use Avax\Cache\System\PublicSurface\CacheReadTarget;
use Avax\Cache\System\PublicSurface\CacheReadTargetWasNotSupported;
use Avax\Cache\System\PublicSurface\CompiledCacheTarget;
use Avax\Cache\System\PublicSurface\ReadFromCache;
use Avax\Cache\System\PublicSurface\RuntimeCacheTarget;
use DateInterval;
use Throwable;

final class Cache
{
    private static ?CacheContract $instance = null;

    public static function use(CacheContract $cache) : void
    {
        self::$instance = $cache;
    }

    public static function reset() : void
    {
        self::$instance = null;
    }

    public static function get(string $key, mixed $default = null) : mixed
    {
        return self::resolve()->get($key, $default);
    }

    public static function set(string $key, mixed $value, null|int|DateInterval $ttl = null) : bool
    {
        return self::resolve()->set($key, $value, $ttl);
    }

    public static function put(string $key, mixed $value, null|int|DateInterval $ttl = null) : bool
    {
        return self::resolve()->set($key, $value, $ttl);
    }

    public static function remember(string $key, null|int|DateInterval $ttl, callable $loader) : mixed
    {
        return self::resolve()->remember($key, $ttl, $loader);
    }

    public static function forget(string $key) : bool
    {
        return self::resolve()->delete($key);
    }

    public static function clear() : bool
    {
        return self::resolve()->clear();
    }

    public static function has(string $key) : bool
    {
        return self::resolve()->has($key);
    }

    public static function store(?string $name = null) : CacheContract
    {
        if ($name !== null) {
            return self::resolveFacade()->store($name);
        }

        return self::resolve();
    }

    public static function read(CacheReadTarget|string $target, mixed $default = null) : mixed
    {
        if (is_string($target)) {
            return self::resolve()->get($target, $default);
        }

        if ($target instanceof RuntimeCacheTarget) {
            return self::readRuntimeTarget($target);
        }

        if ($target instanceof CompiledCacheTarget) {
            return self::resolveReadFromCache()->read($target);
        }

        throw new CacheReadTargetWasNotSupported($target::class);
    }

    private static function readRuntimeTarget(RuntimeCacheTarget $target) : mixed
    {
        if ($target->store !== null) {
            return self::resolveFacade()->read($target);
        }

        return self::resolve()->get($target->key, $target->default);
    }

    private static function resolveFacade() : CacheFacade
    {
        if (function_exists('app')) {
            try {
                $app = app();
                if ($app->has(CacheFacade::class)) {
                    return $app(CacheFacade::class);
                }
            } catch (Throwable) {
            }
        }

        throw new CacheNotConfigured(
            'Named store requires CacheServiceProvider with CacheFacade bound.'
        );
    }

    private static function resolveReadFromCache() : ReadFromCache
    {
        if (function_exists('app')) {
            try {
                $app = app();
                if ($app->has(ReadFromCache::class)) {
                    return $app(ReadFromCache::class);
                }
            } catch (Throwable) {
            }
        }

        throw new CacheNotConfigured(
            'CompiledCacheTarget requires ReadFromCache to be bound. Use CacheServiceProvider.'
        );
    }

    private static function resolve() : CacheContract
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        if (function_exists('app')) {
            try {
                $app = app();
                if ($app->has(CacheFacade::class)) {
                    return $app(CacheFacade::class);
                }
            } catch (Throwable $e) {
                if (str_contains($e->getMessage(), 'Container instance is not initialized')) {
                    throw new CacheNotConfigured();
                }
                throw $e;
            } catch (Throwable) {
            }
        }

        throw new CacheNotConfigured();
    }
}