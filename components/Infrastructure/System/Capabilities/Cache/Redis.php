<?php

declare(strict_types=1);

namespace Avax\Components\Infrastructure\System\Capabilities\Cache;

use Redis as PhpRedis;

/**
 * Concrete Redis implementation of the Driver interface.
 */
final class Redis implements Driver
{
    private ?PhpRedis $redis = null;

    public function __construct(
        private readonly string $host = '127.0.0.1',
        private readonly int $port = 6379,
        private readonly ?string $auth = null,
        private readonly int $database = 0,
        private readonly float $timeout = 5.0
    ) {}

    private function ensureConnected(): PhpRedis
    {
        if ($this->redis === null) {
            $this->redis = new PhpRedis();
            $this->redis->connect($this->host, $this->port, $this->timeout);
            if ($this->auth) {
                $this->redis->auth($this->auth);
            }
            if ($this->database > 0) {
                $this->redis->select($this->database);
            }
        }
        return $this->redis;
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        return $this->ensureConnected()->set($key, $value, $ttl ?? 0);
    }

    public function get(string $key): mixed
    {
        return $this->ensureConnected()->get($key);
    }

    public function del(string $key): int
    {
        return (int) $this->ensureConnected()->del($key);
    }

    /**
     * @param array<string, mixed> $dictionary
     */
    public function hMSet(string $key, array $dictionary): bool
    {
        return $this->ensureConnected()->hMSet($key, $dictionary);
    }

    /**
     * @return array<string, mixed>
     */
    public function hGetAll(string $key): array
    {
        return $this->ensureConnected()->hGetAll($key);
    }

    public function expire(string $key, int $seconds): bool
    {
        return $this->ensureConnected()->expire($key, $seconds);
    }

    public function sAdd(string $key, string $value): int
    {
        return (int) $this->ensureConnected()->sAdd($key, $value);
    }

    public function sRem(string $key, string $value): int
    {
        return (int) $this->ensureConnected()->sRem($key, $value);
    }

    /**
     * @return list<string>
     */
    public function sMembers(string $key): array
    {
        return $this->ensureConnected()->sMembers($key);
    }
}
