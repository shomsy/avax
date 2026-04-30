<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Stores;

use Avax\Components\Application\Cache\System\Foundation\CacheStoreInterface;
use Memcached;
use Redis;
use RuntimeException;

final class RedisCacheStore implements CacheStoreInterface
{
    private Redis  $redis;
    private string $prefix;

    public function __construct(
        private array $config = [],
    )
    {
        $this->prefix = $config['prefix'] ?? 'cache_';

        $this->redis = new Redis();
        $this->connect();
    }

    private function connect() : void
    {
        $host     = $this->config['host'] ?? '127.0.0.1';
        $port     = $this->config['port'] ?? 6379;
        $password = $this->config['password'] ?? null;
        $database = $this->config['database'] ?? 0;

        if (! $this->redis->connect($host, $port)) {
            throw new RuntimeException("Cannot connect to Redis at {$host}:{$port}");
        }

        if ($password !== null) {
            $this->redis->auth($password);
        }

        if ($database > 0) {
            $this->redis->select($database);
        }
    }

    public function forget(string $key) : bool
    {
        return $this->redis->del($this->prefix . $key) > 0;
    }

    public function flush() : bool
    {
        return $this->redis->flushDB();
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
        return $this->redis->exists($this->prefix . $key) > 0;
    }

    public function get(string $key) : mixed
    {
        $value = $this->redis->get($this->prefix . $key);

        return $value === false ? null : unserialize($value);
    }

    public function set(string $key, mixed $value, int $ttl = 0) : bool
    {
        $serialized = serialize($value);

        if ($ttl > 0) {
            return $this->redis->setex($this->prefix . $key, $ttl, $serialized);
        }

        return $this->redis->set($this->prefix . $key, $serialized);
    }

    public function tags(array $tags) : TaggedCache
    {
        return new TaggedCache($this, $tags);
    }
}

final class TaggedCache
{
    private string $tagKey;

    public function __construct(
        private RedisCacheStore $store,
        private array           $tags,
    )
    {
        $this->tagKey = 'tag:' . implode(':', $tags);
    }

    public function get(string $key) : mixed
    {
        return $this->store->get($this->tagKey . ':' . $key);
    }

    public function set(string $key, mixed $value, int $ttl = 0) : bool
    {
        $this->store->getRedis()->sAdd($this->tagKey, $key);

        return $this->store->set($this->tagKey . ':' . $key, $value, $ttl);
    }

    public function flush() : bool
    {
        $keys = $this->store->getRedis()->sMembers($this->tagKey);

        foreach ($keys as $key) {
            $this->store->forget($key);
        }

        return $this->store->getRedis()->del($this->tagKey) > 0;
    }
}

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