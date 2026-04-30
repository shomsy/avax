<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Stores;

use Avax\Components\Application\Cache\System\Foundation\CacheStoreInterface;
use Memcached;
use Redis;
use RuntimeException;



final class MemcachedCacheStore implements CacheStoreInterface
{
    private Memcached $memcached;

    public function __construct(
        private array $config = [],
    )
    {
        $this->memcached = new Memcached();
        $this->connect();
    }

    private function connect() : void
    {
        $servers = $this->config['servers'] ?? [['127.0.0.1', 11211]];

        foreach ($servers as $server) {
            $this->memcached->addServer($server[0], $server[1]);
        }
    }

    public function forget(string $key) : bool
    {
        return $this->memcached->delete($key);
    }

    public function flush() : bool
    {
        return $this->memcached->flush();
    }

    public function remember(string $key, int $ttl, callable $callback) : mixed
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        $value = $callback();
        $this->set($key, $value, $ttl);

        return $value;
    }

    public function has(string $key) : bool
    {
        $this->memcached->get($key);

        return $this->memcached->getResultCode() !== Memcached::RES_NOTFOUND;
    }

    public function get(string $key) : mixed
    {
        $value = $this->memcached->get($key);

        return $value === false && $this->memcached->getResultCode() === Memcached::RES_NOTFOUND ? null : $value;
    }

    public function set(string $key, mixed $value, int $ttl = 0) : bool
    {
        return $this->memcached->set($key, $value, $ttl);
    }

    public function tags(array $tags) : TaggedCache
    {
        return new TaggedCache($this, $tags);
    }
}