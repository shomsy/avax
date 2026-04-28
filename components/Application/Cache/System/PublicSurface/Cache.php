<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\PublicSurface;

use Avax\Cache\System\CacheContract;
use DateInterval;

/**
 * Cache Public Surface.
 *
 * Thin entry point for the Cache component.
 * Delegated to internal capabilities (Storage, Lifecycle).
 */
final readonly class Cache
{
    public function __construct(
        private CacheContract $driver
    ) {}

    public function get(string $key, mixed $default = null) : mixed
    {
        return $this->driver->get($key, $default);
    }

    public function set(string $key, mixed $value, int|DateInterval|null $ttl = null) : bool
    {
        return $this->driver->set($key, $value, $ttl);
    }

    public function has(string $key) : bool
    {
        return $this->driver->has($key);
    }

    public function forget(string $key) : bool
    {
        return $this->driver->delete($key);
    }

    public function clear() : bool
    {
        return $this->driver->clear();
    }
}
