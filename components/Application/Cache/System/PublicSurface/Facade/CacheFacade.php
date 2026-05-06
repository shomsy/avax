<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\PublicSurface\Facade;

use Avax\Components\Application\Cache\System\CacheContract;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheContract;
use Avax\Components\Application\Cache\System\PublicSurface\Read\CacheReadTarget;
use Avax\Components\Application\Cache\System\PublicSurface\Read\ReadFromCache;
use DateInterval;

readonly class CacheFacade
{
    public function __construct(
        private CacheRegistry $cacheRegistry,
        private ?CompiledCacheContract $compiledCacheContract = null,
    ) {
    }

    public function put(string $key, mixed $value, int|DateInterval|null $ttl = null) : bool
    {
        return $this->set($key, $value, $ttl);
    }

    public function set(string $key, mixed $value, int|DateInterval|null $ttl = null) : bool
    {
        return $this->cacheRegistry->default()->set($key, $value, $ttl);
    }

    public function remember(string $key, int|DateInterval|null $ttl, callable $loader) : mixed
    {
        return $this->cacheRegistry->default()->remember($key, $ttl, $loader);
    }

    public function forget(string $key) : bool
    {
        return $this->cacheRegistry->default()->delete($key);
    }

    public function clear(): bool
    {
        return $this->cacheRegistry->default()->clear();
    }

    public function has(string $key): bool
    {
        return $this->cacheRegistry->default()->has(key: $key);
    }

    public function store(?string $name = null): CacheContract
    {
        if ($name === null) {
            return $this->cacheRegistry->default();
        }

        return $this->cacheRegistry->get(name: $name);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->cacheRegistry->default()->get(key: $key, default: $default);
    }

    public function read(CacheReadTarget|string $target, mixed $default = null) : mixed
    {
        if (is_string($target)) {
            return $this->cacheRegistry->default()->get($target, $default);
        }

        $readFromCache = new ReadFromCache($this->cacheRegistry, $this->compiledCacheContract);

        return $readFromCache->read($target, $default);
    }
}
