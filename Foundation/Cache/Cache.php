<?php

declare(strict_types=1);

namespace Avax\Cache;

use Avax\Cache\System\CacheContract;
use Avax\Cache\System\PublicSurface\Exception\NotConfigured;
use Avax\Cache\System\PublicSurface\Exception\UnsupportedTarget;
use Avax\Cache\System\PublicSurface\Facade\CacheFacade;
use Avax\Cache\System\PublicSurface\Read\CacheReadTarget;
use Avax\Cache\System\PublicSurface\Read\CompiledCacheTarget;
use Avax\Cache\System\PublicSurface\Read\ReadFromCache;
use Avax\Cache\System\PublicSurface\Read\RuntimeCacheTarget;
use DateInterval;
use Psr\SimpleCache\InvalidArgumentException;
use Throwable;

final class Cache
{
    private static CacheContract|null $instance = null;

    public static function use(CacheContract $cache) : void
    {
        self::$instance = $cache;
    }

    public static function reset() : void
    {
        self::$instance = null;
    }

    /**
     * @throws Throwable
     * @throws InvalidArgumentException
     */
    public static function get(string $key, mixed $default = null) : mixed
    {
        return self::resolve()->get(key: $key, default: $default);
    }

    /**
     * @throws Throwable
     * @throws InvalidArgumentException
     */
    public static function set(string $key, mixed $value, int|DateInterval|null $ttl = null) : bool
    {
        return self::resolve()->set(key: $key, value: $value, ttl: $ttl);
    }

    /**
     * @throws Throwable
     * @throws InvalidArgumentException
     */
    public static function put(string $key, mixed $value, int|DateInterval|null $ttl = null) : bool
    {
        return self::resolve()->set(key: $key, value: $value, ttl: $ttl);
    }

    /**
     * @throws Throwable
     */
    public static function remember(string $key, int|DateInterval|null $ttl, callable $loader) : mixed
    {
        return self::resolve()->remember(key: $key, ttl: $ttl, loader: $loader);
    }

    /**
     * @throws Throwable
     * @throws InvalidArgumentException
     */
    public static function forget(string $key) : bool
    {
        return self::resolve()->delete(key: $key);
    }

    /**
     * @throws Throwable
     */
    public static function clear() : bool
    {
        return self::resolve()->clear();
    }

    /**
     * @throws Throwable
     * @throws InvalidArgumentException
     */
    public static function has(string $key) : bool
    {
        return self::resolve()->has(key: $key);
    }

    /**
     * @throws Throwable
     */
    public static function store(string|null $name = null) : CacheContract
    {
        if ($name !== null) {
            return self::resolveFacade()->store(name: $name);
        }

        return self::resolve();
    }

    /**
     * @throws Throwable
     * @throws InvalidArgumentException
     */
    public static function read(CacheReadTarget|string $target, mixed $default = null) : mixed
    {
        if (is_string($target)) {
            return self::resolve()->get(key: $target, default: $default);
        }

        if ($target instanceof RuntimeCacheTarget) {
            return self::readRuntimeTarget(target: $target);
        }

        if ($target instanceof CompiledCacheTarget) {
            return self::resolveReadFromCache()->read(target: $target);
        }

        throw new UnsupportedTarget(targetClass: $target::class);
    }

    /**
     * @throws Throwable
     * @throws InvalidArgumentException
     */
    private static function readRuntimeTarget(RuntimeCacheTarget $target) : mixed
    {
        if ($target->store !== null) {
            return self::resolveFacade()->read(target: $target);
        }

        return self::resolve()->get(key: $target->key, default: $target->default);
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

        throw new NotConfigured(
            message: 'Named store requires CacheServiceProvider with CacheFacade bound.'
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

        throw new NotConfigured(
            message: 'CompiledCacheTarget requires ReadFromCache to be bound. Use CacheServiceProvider.'
        );
    }

    /**
     * @throws Throwable
     */
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
                    throw new NotConfigured();
                }
                throw $e;
            }
        }

        throw new NotConfigured();
    }
}