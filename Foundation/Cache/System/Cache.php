<?php

declare(strict_types=1);

namespace Avax\Cache\System;

use DateInterval;

final class Cache
{
    private static ?CacheRegistry $registry = null;

    public static function put(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        return self::set($key, $value, $ttl);
    }

    public static function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        return self::registry()->default()->set($key, $value, $ttl);
    }

    private static function registry(): CacheRegistry
    {
        if (self::$registry === null) {
            self::$registry = new CacheRegistry();
        }

        return self::$registry;
    }

    public static function remember(string $key, null|int|DateInterval $ttl, callable $loader): mixed
    {
        return self::registry()->default()->remember($key, $ttl, $loader);
    }

    public static function forget(string $key): bool
    {
        return self::delete($key);
    }

    public static function delete(string $key): bool
    {
        return self::registry()->default()->delete($key);
    }

    public static function clear(): bool
    {
        return self::registry()->default()->clear();
    }

    public static function has(string $key): bool
    {
        return self::registry()->default()->has($key);
    }

    public static function store(?string $name = null): CacheContract
    {
        if ($name === null) {
            return self::registry()->default();
        }

        return self::registry()->get($name);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::registry()->default()->get($key, $default);
    }

    public static function use(CacheContract $cache, string $name = 'default'): void
    {
        self::registry()->register($name, $cache);
    }

    public static function reset(): void
    {
        self::$registry = null;
    }
}