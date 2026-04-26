<?php

declare(strict_types=1);

namespace Avax\Cache\System\PublicSurface\Facade;

use Avax\Cache\System\CacheContract;
use Avax\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheContract;
use Avax\Cache\System\PublicSurface\Read\CacheReadTarget;
use Avax\Cache\System\PublicSurface\Read\ReadFromCache;
use DateInterval;
use Psr\SimpleCache\InvalidArgumentException;

final readonly class CacheFacade
{
    public function __construct(
        private CacheRegistry              $registry,
        private CompiledCacheContract|null $compiledCache = null,
    ) {}

    public function put(string $key, mixed $value, int|DateInterval|null $ttl = null) : bool
    {
        return $this->set(key: $key, value: $value, ttl: $ttl);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function set(string $key, mixed $value, int|DateInterval|null $ttl = null) : bool
    {
        return $this->registry->default()->set(key: $key, value: $value, ttl: $ttl);
    }

    public function remember(string $key, int|DateInterval|null $ttl, callable $loader) : mixed
    {
        return $this->registry->default()->remember(key: $key, ttl: $ttl, loader: $loader);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function forget(string $key) : bool
    {
        return $this->registry->default()->delete(key: $key);
    }

    public function clear() : bool
    {
        return $this->registry->default()->clear();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function has(string $key) : bool
    {
        return $this->registry->default()->has(key: $key);
    }

    public function store(string|null $name = null) : CacheContract
    {
        if ($name === null) {
            return $this->registry->default();
        }

        return $this->registry->get(name: $name);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function get(string $key, mixed $default = null) : mixed
    {
        return $this->registry->default()->get(key: $key, default: $default);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function read(CacheReadTarget|string $target, mixed $default = null) : mixed
    {
        if (is_string($target)) {
            return $this->registry->default()->get(key: $target, default: $default);
        }

        $reader = new ReadFromCache(runtimeCaches: $this->registry, compiledCache: $this->compiledCache);

        return $reader->read(target: $target, default: $default);
    }
}