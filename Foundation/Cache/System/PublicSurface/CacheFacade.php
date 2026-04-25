<?php

declare(strict_types=1);

namespace Avax\Cache\System\PublicSurface;

use Avax\Cache\System\CacheContract;
use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCacheContract;

final readonly class CacheFacade
{
    public function __construct(
        private CacheRegistry          $registry,
        private ?CompiledCacheContract $compiledCache = null,
    ) {}

    public function get(string $key, mixed $default = null) : mixed
    {
        return $this->registry->default()->get($key, $default);
    }

    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null) : bool
    {
        return $this->registry->default()->set($key, $value, $ttl);
    }

    public function put(string $key, mixed $value, null|int|\DateInterval $ttl = null) : bool
    {
        return $this->set($key, $value, $ttl);
    }

    public function remember(string $key, null|int|\DateInterval $ttl, callable $loader) : mixed
    {
        return $this->registry->default()->remember($key, $ttl, $loader);
    }

    public function forget(string $key) : bool
    {
        return $this->registry->default()->delete($key);
    }

    public function clear() : bool
    {
        return $this->registry->default()->clear();
    }

    public function has(string $key) : bool
    {
        return $this->registry->default()->has($key);
    }

    public function store(?string $name = null) : CacheContract
    {
        if ($name === null) {
            return $this->registry->default();
        }

        return $this->registry->get($name);
    }

    public function read(CacheReadTarget|string $target, mixed $default = null) : mixed
    {
        if (is_string($target)) {
            return $this->registry->default()->get($target, $default);
        }

        $reader = new ReadFromCache($this->registry, $this->compiledCache);

        return $reader->read($target, $default);
    }
}