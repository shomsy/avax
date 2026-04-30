<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Capabilities\Storage;

use Avax\Components\HTTP\Session\System\Foundation\SessionData;
use Avax\Components\HTTP\Session\System\Foundation\SessionStoreInterface;
use Redis;
use RuntimeException;

final class RedisSessionStore implements SessionStoreInterface
{
    private Redis  $redis;
    private string $prefix;
    private int    $ttl;

    public function __construct(
        private array $config = [],
    )
    {
        $this->prefix = $config['prefix'] ?? 'sess_';
        $this->ttl    = $config['ttl'] ?? 1200;

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

    public function read(string $sessionId) : SessionData|null
    {
        $key  = $this->prefix . $sessionId;
        $data = $this->redis->get($key);

        if ($data === false) {
            return null;
        }

        $decoded = json_decode($data, true);

        return new SessionData(
            id       : $sessionId,
            data     : $decoded['data'] ?? [],
            createdAt: $decoded['created_at'] ?? time(),
            updatedAt: $decoded['updated_at'] ?? time(),
        );
    }

    public function write(string $sessionId, SessionData $data) : bool
    {
        $key     = $this->prefix . $sessionId;
        $payload = json_encode([
                                   'data'       => $data->data,
                                   'created_at' => $data->createdAt,
                                   'updated_at' => time(),
                               ]);

        return $this->redis->setex($key, $this->ttl, $payload);
    }

    public function destroy(string $sessionId) : bool
    {
        $key = $this->prefix . $sessionId;

        return $this->redis->del($key) > 0;
    }

    public function exists(string $sessionId) : bool
    {
        $key = $this->prefix . $sessionId;

        return $this->redis->exists($key) > 0;
    }

    public function gc(int $maxLifetime) : int
    {
        return 0;
    }
}