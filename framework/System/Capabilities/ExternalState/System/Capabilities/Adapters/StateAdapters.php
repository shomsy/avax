<?php

declare(strict_types=1);

namespace Avax\Components\ExternalState\System\Capabilities\Adapters;

use Avax\Components\ExternalState\System\PublicSurface\StateAdapter;
use Redis;

final class RedisStateAdapter implements StateAdapter
{
    private Redis $redis;
    private string $prefix;

    public function __construct(string $url)
    {
        $this->redis = new Redis();
        $this->redis->connect('127.0.0.1', 6379);
        $this->prefix = 'avax:';
    }

    public function get(string $key) : mixed
    {
        $value = $this->redis->get($this->prefix . $key);

        return $value !== false ? unserialize($value) : null;
    }

    public function set(string $key, mixed $value, int $ttl = 0) : void
    {
        $serialized = serialize($value);

        if ($ttl > 0) {
            $this->redis->setex($this->prefix . $key, $ttl, $serialized);
        } else {
            $this->redis->set($this->prefix . $key, $serialized);
        }
    }

    public function delete(string $key) : void
    {
        $this->redis->del($this->prefix . $key);
    }

    public function exists(string $key) : bool
    {
        $result = $this->redis->exists($this->prefix . $key);

        return is_int($result) ? $result > 0 : (bool) $result;
    }

    public function increment(string $key, int $value = 1) : int
    {
        return (int) $this->redis->incrby($this->prefix . $key, $value);
    }

    public function expire(string $key, int $ttl) : void
    {
        $this->redis->expire($this->prefix . $key, $ttl);
    }
}

final class MemoryStateAdapter implements StateAdapter
{
    /** @var array<string, mixed> */
    private array $store = [];
    /** @var array<string, int> */
    private array $ttls = [];

    public function get(string $key) : mixed
    {
        if (isset($this->ttls[$key])) {
            if ($this->ttls[$key] < time()) {
                unset($this->store[$key], $this->ttls[$key]);

                return null;
            }
        }

        return $this->store[$key] ?? null;
    }

    public function set(string $key, mixed $value, int $ttl = 0) : void
    {
        $this->store[$key] = $value;

        if ($ttl > 0) {
            $this->ttls[$key] = time() + $ttl;
        } else {
            unset($this->ttls[$key]);
        }
    }

    public function delete(string $key) : void
    {
        unset($this->store[$key], $this->ttls[$key]);
    }

    public function exists(string $key) : bool
    {
        return isset($this->store[$key]) && (! isset($this->ttls[$key]) || $this->ttls[$key] >= time());
    }

    public function increment(string $key, int $value = 1) : int
    {
        $current           = (int) ($this->store[$key] ?? 0);
        $new               = $current + $value;
        $this->store[$key] = $new;

        return $new;
    }

    public function expire(string $key, int $ttl) : void
    {
        $this->ttls[$key] = time() + $ttl;
    }
}
