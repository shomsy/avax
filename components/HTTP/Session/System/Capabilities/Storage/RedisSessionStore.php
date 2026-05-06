<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Capabilities\Storage;

use Redis;
use Throwable;

final class RedisSessionStore implements SessionStoreInterface
{
    /** @var array<string, array<string, mixed>> */
    private array $fallback = [];

    private ?Redis $redis = null;

    private readonly string $prefix;

    private readonly int $ttl;

    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(private array $config = [])
    {
        $this->prefix = $config['prefix'] ?? 'sess_';
        $this->ttl = $config['ttl'] ?? 1200;
        $this->connect();
    }

    /**
     * @return array<string, mixed>
     */
    public function read(string $id): array
    {
        if (! $this->redis instanceof Redis) {
            return $this->fallback[$id] ?? [];
        }

        $payload = $this->redis->get($this->key(sessionId: $id));

        if ($payload === false) {
            return [];
        }

        $decoded = json_decode(json: (string) $payload, associative: true);

        return is_array(value: $decoded['data'] ?? null) ? $decoded['data'] : [];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function write(string $id, array $data): bool
    {
        if (! $this->redis instanceof Redis) {
            $this->fallback[$id] = $data;

            return true;
        }

        $payload = json_encode(value: ['data' => $data, 'updated_at' => time()], flags: JSON_THROW_ON_ERROR);

        return (bool) $this->redis->setex($this->key(sessionId: $id), $this->ttl, $payload);
    }

    public function destroy(string $id): bool
    {
        if (! $this->redis instanceof Redis) {
            unset($this->fallback[$id]);

            return true;
        }

        return $this->redis->del($this->key(sessionId: $id)) >= 0;
    }

    public function exists(string $sessionId): bool
    {
        if (! $this->redis instanceof Redis) {
            return isset($this->fallback[$sessionId]);
        }

        return $this->redis->exists($this->key(sessionId: $sessionId)) > 0;
    }

    public function gc(int $maxLifetime): int
    {
        return 0;
    }

    private function key(string $sessionId): string
    {
        return $this->prefix.$sessionId;
    }

    private function connect(): void
    {
        if (($this->config['driver'] ?? 'auto') === 'array' || ! class_exists(class: Redis::class)) {
            return;
        }

        try {
            $redis = new Redis();
            $redis->connect(
                host   : $this->config['host'] ?? '127.0.0.1',
                port   : $this->config['port'] ?? 6379,
                timeout: $this->config['timeout'] ?? 0.05,
            );
            $this->redis = $redis;
        } catch (Throwable) {
            $this->redis = null;
        }
    }
}
