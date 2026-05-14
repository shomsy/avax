<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\PublicSurface;

use Avax\Components\Application\Cache\System\CacheContract;
use DateInterval;

/**
 * Cache Public Surface.
 *
 * Thin entry point for the Cache component.
 * Delegated to internal capabilities (Storage, Lifecycle).
 */
final readonly class CacheContractFacade
{
    public function __construct(
        private CacheContract $cacheContract,
    ) {}

    public function get(string $key, mixed $default = null) : mixed
    {
        return $this->cacheContract->get($key, $default);
    }

    public function set(string $key, mixed $value, int|DateInterval|null $ttl = null) : bool
    {
        return $this->cacheContract->set($key, $value, $ttl);
    }

    public function has(string $key) : bool
    {
        return $this->cacheContract->has($key);
    }

    public function forget(string $key) : bool
    {
        return $this->cacheContract->delete($key);
    }

    public function clear() : bool
    {
        return $this->cacheContract->clear();
    }
}
