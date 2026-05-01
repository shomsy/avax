<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Stores;

use Avax\Components\Application\Cache\System\Foundation\CacheStoreInterface;
use Memcached;
use Override;


final class MemcachedCacheStore implements CacheStoreInterface
{
    private readonly Memcached $memcached;

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

    #[Override]
    public function forget(string $key) : bool
    {
        return $this->memcached->delete($key);
    }

    #[Override]
    public function flush() : bool
    {
        return $this->memcached->flush();
    }

    #[Override]
    public function remember(string $key, int $ttl, callable $callback) : mixed
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        $value = $callback();
        $this->set($key, $value, $ttl);

        return $value;
    }

    #[Override]
    public function has(string $key) : bool
    {
        $this->memcached->get($key);

        return $this->memcached->getResultCode() !== Memcached::RES_NOTFOUND;
    }

    #[Override]
    public function get(string $key) : mixed
    {
        $value = $this->memcached->get($key);

        return $value === false && $this->memcached->getResultCode() === Memcached::RES_NOTFOUND ? null : $value;
    }

    #[Override]
    public function set(string $key, mixed $value, int $ttl = 0) : bool
    {
        return $this->memcached->set($key, $value, $ttl);
    }

    #[Override]
    public function tags(array $tags) : TaggedCache
    {
        return new TaggedCache($this, $tags);
    }
}