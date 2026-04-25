<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\StoreCachedValues;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Capabilities\ManageCacheLifecycle\CachedValueLifecycle;
use Avax\Cache\System\Foundation\Time\Clock;
use Avax\Cache\System\Foundation\Time\SystemClock;
use Avax\Cache\System\Foundation\Time\Timestamp;
use Redis;
use RedisArray;
use RedisCluster;

final class RedisCacheStore implements CacheStore
{
    private const DEFAULT_PORT    = 6379;
    private const DEFAULT_TIMEOUT = 5.0;
    private const DEFAULT_PREFIX  = 'avax_cache:';

    private Redis|RedisArray|RedisCluster|null $redis     = null;
    private bool                               $connected = false;

    public function __construct(
        private string  $host = '127.0.0.1',
        private int     $port = self::DEFAULT_PORT,
        private ?string $password = null,
        private int     $database = 0,
        private float   $timeout = self::DEFAULT_TIMEOUT,
        private string  $prefix = self::DEFAULT_PREFIX,
        private Clock   $clock = new SystemClock()
    ) {}

    public function read(CacheKey $key, Clock $clock) : CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        $this->ensureConnected();

        $fullKey = $this->prefix . $key->fullKey();
        $data    = $this->redis->get($fullKey);

        if ($data === false || $data === null) {
            return new CacheStoreRecordWasMissing($key);
        }

        $decoded = json_decode($data, associative: true);

        if ($decoded === null) {
            return new CacheStoreRecordWasMissing($key);
        }

        $lifecycle = $this->deserializeLifecycle($decoded['lifecycle'] ?? [], $clock);

        if ($lifecycle->isExpired($clock)) {
            $this->forget($key);

            return new CacheStoreRecordWasMissing($key);
        }

        $updatedLifecycle = $lifecycle->withAccessed($clock);
        $record           = new StoredCacheRecord(
            value    : $decoded['value'] ?? null,
            lifecycle: $updatedLifecycle
        );

        return new CacheStoreRecordWasFound($key, $record, $clock);
    }

    private function ensureConnected() : void
    {
        if (! $this->connected) {
            $this->connect();
        }
    }

    public function connect() : void
    {
        if ($this->connected) {
            return;
        }

        $this->redis = new Redis();
        $this->redis->connect($this->host, $this->port, $this->timeout);

        if ($this->password !== null) {
            $this->redis->auth($this->password);
        }

        if ($this->database > 0) {
            $this->redis->select($this->database);
        }

        $this->connected = true;
    }

    private function deserializeLifecycle(array $data, Clock $clock) : CachedValueLifecycle
    {
        return new CachedValueLifecycle(
            createdAt     : Timestamp::fromUnixTime($data['createdAt'] ?? time()),
            lastAccessedAt: Timestamp::fromUnixTime($data['lastAccessedAt'] ?? time()),
            expiresAt     : Timestamp::fromUnixTime($data['expiresAt'] ?? time()),
            refreshedAt   : Timestamp::fromUnixTime($data['refreshedAt'] ?? time()),
            hitCount      : $data['hitCount'] ?? 0,
            refreshCount  : $data['refreshCount'] ?? 0
        );
    }

    public function forget(CacheKey $key) : void
    {
        $this->ensureConnected();

        $fullKey = $this->prefix . $key->fullKey();
        $this->redis->del($fullKey);
    }

    public function write(CacheKey $key, StoredCacheRecord $record) : void
    {
        $this->ensureConnected();

        $fullKey = $this->prefix . $key->fullKey();

        $data = json_encode([
                                'value' => $record->value,
                                                                                                                                                                                                                                                                                                     'lifecycle' => $this->serializeLifecycle($record->lifecycle),
                            ], JSON_THROW_ON_ERROR);

        $ttl = $record->lifecycle->timeToLive($this->clock);

        if ($ttl > 0 && $ttl < PHP_INT_MAX) {
            $this->redis->setex($fullKey, $ttl, $data);
        } else {
            $this->redis->set($fullKey, $data);
        }
    }

    private function serializeLifecycle(CachedValueLifecycle $lifecycle) : array
    {
        return [
            'createdAt'      => $lifecycle->createdAt->toUnixTime(),
            'lastAccessedAt' => $lifecycle->lastAccessedAt->toUnixTime(),
            'expiresAt'      => $lifecycle->expiresAt->toUnixTime(),
            'refreshedAt'    => $lifecycle->refreshedAt->toUnixTime(),
            'hitCount'       => $lifecycle->hitCount,
            'refreshCount'   => $lifecycle->refreshCount,
        ];
    }

    public function clear() : void
    {
        $this->ensureConnected();

        $keys = $this->redis->keys($this->prefix . '*');

        if (! empty($keys)) {
            $this->redis->del($keys);
        }
    }

    public function exists(CacheKey $key) : bool
    {
        $this->ensureConnected();

        $fullKey = $this->prefix . $key->fullKey();

        return (bool) $this->redis->exists($fullKey);
    }

    public function increment(string $key, int $value = 1) : int
    {
        $this->ensureConnected();

        $fullKey = $this->prefix . $key;

        return (int) $this->redis->incrBy($fullKey, $value);
    }

    public function decrement(string $key, int $value = 1) : int
    {
        $this->ensureConnected();

        $fullKey = $this->prefix . $key;

        return (int) $this->redis->decrBy($fullKey, $value);
    }

    public function isConnected() : bool
    {
        return $this->connected;
    }

    public function disconnect() : void
    {
        if ($this->redis !== null && $this->connected) {
            $this->redis->close();
            $this->connected = false;
        }
    }
}