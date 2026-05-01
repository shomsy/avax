<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache;

use Avax\Components\Application\Cache\System\CacheContract;
use Avax\Components\Application\Cache\System\PublicSurface\CacheReadTarget;
use Avax\Components\Application\Cache\System\PublicSurface\Exception\InvalidTarget;
use Avax\Components\Application\Cache\System\PublicSurface\Exception\NotConfigured;
use Avax\Components\Application\Cache\System\PublicSurface\Read\CompiledCacheTarget;
use Avax\Components\Application\Cache\System\PublicSurface\Read\RuntimeCacheTarget;
use DateInterval;

/**
 * Stable static entrypoint for the Cache component.
 */
final class Cache
{
    private static CacheContract|null $default = null;

    public static function use(CacheContract $cache) : void
    {
        self::$default = $cache;
    }

    public static function reset() : void
    {
        self::$default = null;
    }

    public static function put(string $key, mixed $value, int|DateInterval|null $ttl = null) : bool
    {
        return self::set(key: $key, value: $value, ttl: $ttl);
    }

    public static function set(string $key, mixed $value, int|DateInterval|null $ttl = null) : bool
    {
        return self::default()->set(key: $key, value: $value, ttl: $ttl);
    }

    private static function default() : CacheContract
    {
        if (self::$default === null) {
            throw new NotConfigured();
        }

        return self::$default;
    }

    public static function remember(string $key, int|DateInterval|null $ttl, callable $loader) : mixed
    {
        return self::default()->remember(key: $key, ttl: $ttl, loader: $loader);
    }

    public static function forget(string $key) : bool
    {
        return self::default()->delete(key: $key);
    }

    public static function clear() : bool
    {
        return self::default()->clear();
    }

    public static function has(string $key) : bool
    {
        return self::default()->has(key: $key);
    }

    public static function read(CacheReadTarget|string $target, mixed $default = null) : mixed
    {
        if (is_string($target)) {
            return self::get(key: $target, default: $default);
        }

        if ($target instanceof RuntimeCacheTarget) {
            if ($target->store !== null) {
                return self::store(name: $target->store)->get(key: $target->key, default: $target->default);
            }

            return self::default()->get(key: $target->key, default: $target->default ?? $default);
        }

        if ($target instanceof CompiledCacheTarget) {
            return CompiledCache::read(name: $target->name, build: $target->builder(), sources: $target->sources);
        }

        throw new InvalidTarget(targetClass: $target::class);
    }

    public static function get(string $key, mixed $default = null) : mixed
    {
        return self::default()->get(key: $key, default: $default);
    }

    public static function store(string|null $name = null) : CacheContract
    {
        if ($name !== null) {
            throw new NotConfigured(message: 'Named store requires RegisterCacheDependencies');
        }

        return self::default();
    }
}
