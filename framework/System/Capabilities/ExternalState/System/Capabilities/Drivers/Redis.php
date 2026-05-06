<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ExternalState\System\Capabilities\Drivers;

use Avax\Framework\System\Capabilities\ExternalState\System\PublicSurface\State;
use Redis as PhpRedis;

/**
 * Redis implementation of external state.
 */
final readonly class Redis implements State
{
    private PhpRedis $redis;

    private string $prefix;

    public function __construct(string $url = '127.0.0.1:6379')
    {
        $parsed = parse_url($url);
        $host = $parsed['host'] ?? '127.0.0.1';
        $port = (int) ($parsed['port'] ?? 6379);

        $this->redis = new PhpRedis();
        $this->redis->connect($host, $port);

        $this->prefix = 'avax:';
    }

    public function get(string $key): mixed
    {
        $value = $this->redis->get($this->prefix.$key);

        return $value !== false ? unserialize((string) $value) : null;
    }

    public function set(string $key, mixed $value, int $ttl = 0): void
    {
        $serialized = serialize($value);

        if ($ttl > 0) {
            $this->redis->setex($this->prefix.$key, $ttl, $serialized);
        } else {
            $this->redis->set($this->prefix.$key, $serialized);
        }
    }

    public function delete(string $key): void
    {
        $this->redis->del($this->prefix.$key);
    }

    public function exists(string $key): bool
    {
        $result = $this->redis->exists($this->prefix.$key);

        return is_int($result) ? $result > 0 : (bool) $result;
    }

    public function increment(string $key, int $value = 1): int
    {
        return (int) $this->redis->incrby($this->prefix.$key, $value);
    }

    public function expire(string $key, int $ttl): void
    {
        $this->redis->expire($this->prefix.$key, $ttl);
    }
}
