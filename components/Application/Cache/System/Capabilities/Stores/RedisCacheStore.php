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