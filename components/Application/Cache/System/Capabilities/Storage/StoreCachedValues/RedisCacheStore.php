<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\CachedValues\CachedValueLifecycle;
use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Foundation\Serialization\CacheSerializer;
use Avax\Components\Application\Cache\System\Foundation\Serialization\JsonCacheSerializer;
use Avax\Components\Application\Cache\System\Foundation\Serialization\SerializedCachePayload;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;
use JsonException;
use Override;
use Redis;
use RedisArray;
use RedisCluster;
use RedisClusterException;
use SensitiveParameter;

final class RedisCacheStore implements CacheStore
{
    private const int DEFAULT_PORT = 6379;

    private const float DEFAULT_TIMEOUT = 5.0;

    private const string DEFAULT_PREFIX = 'avax_cache:';

    private Redis|RedisArray|RedisCluster|null $redis = null;

    private bool $connected = false;

    private readonly CacheSerializer $cacheSerializer;

    public function __construct(
        private readonly string      $host = '127.0.0.1',
        private readonly int         $port = self::DEFAULT_PORT,
        #[SensitiveParameter]
        private readonly string|null $connectionSecret = null,
        private readonly int         $database = 0,
        private readonly float       $timeout = self::DEFAULT_TIMEOUT,
        private readonly string      $prefix = self::DEFAULT_PREFIX,
        private readonly Clock       $clock = new SystemClock(),
        ?CacheSerializer             $cacheSerializer = null,
    )
    {
        $this->cacheSerializer = $cacheSerializer ?? new JsonCacheSerializer(clock: $this->clock);
    }

    /**
     * @throws RedisClusterException
     */
    #[Override]
    public function read(CacheKey $cacheKey, Clock $clock) : CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        $this->ensureConnected();

        $fullKey = $this->prefix . $cacheKey->fullKey();
        $data    = $this->redis->get($fullKey);

        if ($data === false || $data === null) {
            return new CacheStoreRecordWasMissing(key: $cacheKey);
        }

        $decoded = json_decode($data, associative: true);

        if ($decoded === null) {
            return new CacheStoreRecordWasMissing(key: $cacheKey);
        }

        $cachedValueLifecycle = $this->deserializeLifecycle(data: $decoded['lifecycle'] ?? [], clock: $clock);

        if ($cachedValueLifecycle->isExpired(clock: $clock)) {
            $this->forget(key: $cacheKey);

            return new CacheStoreRecordWasMissing(key: $cacheKey);
        }

        $serializedCachePayload = SerializedCachePayload::create(
            data  : $decoded['value'] ?? '',
            format: $decoded['format'] ?? 'json',
            clock : $clock,
        );

        $value = $this->cacheSerializer->unserialize(payload: $serializedCachePayload);

        $storedCacheRecord = new StoredCacheRecord(
            value    : $value,
            lifecycle: $cachedValueLifecycle,
        );

        return new CacheStoreRecordWasFound(key: $cacheKey, record: $storedCacheRecord, clock: $clock);
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
        $this->redis->connect(host: $this->host, port: $this->port, timeout: $this->timeout);

        if ($this->connectionSecret !== null) {
            $this->redis->auth(credentials: $this->connectionSecret);
        }

        if ($this->database > 0) {
            $this->redis->select($this->database);
        }

        $this->connected = true;
    }

    private function deserializeLifecycle(array $data, Clock $clock) : CachedValueLifecycle
    {
        $now = $clock->now();

        return new CachedValueLifecycle(
            createdAt     : Timestamp::fromUnixTime(timestamp: $data['createdAt'] ?? $now->seconds),
            lastAccessedAt: Timestamp::fromUnixTime(timestamp: $data['lastAccessedAt'] ?? $now->seconds),
            expiresAt     : Timestamp::fromUnixTime(timestamp: $data['expiresAt'] ?? $now->seconds),
            refreshedAt   : Timestamp::fromUnixTime(timestamp: $data['refreshedAt'] ?? $now->seconds),
            hitCount      : $data['hitCount'] ?? 0,
            refreshCount  : $data['refreshCount'] ?? 0,
        );
    }

    /**
     * @throws RedisClusterException
     */
    #[Override]
    public function forget(CacheKey $cacheKey) : void
    {
        $this->ensureConnected();

        $fullKey = $this->prefix . $cacheKey->fullKey();
        $this->redis->del($fullKey);
    }

    /**
     * @throws JsonException
     * @throws RedisClusterException
     */
    #[Override]
    public function write(CacheKey $cacheKey, StoredCacheRecord $storedCacheRecord) : void
    {
        $this->ensureConnected();

        $fullKey = $this->prefix . $cacheKey->fullKey();

        $serializedPayload = $this->cacheSerializer->serialize(value: $storedCacheRecord->value);

        $data = json_encode([
                                'value'     => $serializedPayload->data,
                                'format'    => $serializedPayload->format,
                                'lifecycle' => $this->serializeLifecycle(lifecycle: $storedCacheRecord->lifecycle),
                            ], JSON_THROW_ON_ERROR);

        $ttl = $storedCacheRecord->lifecycle->timeToLive(clock: $this->clock);

        if ($ttl > 0 && $ttl < PHP_INT_MAX) {
            $this->redis->setex($fullKey, $ttl, $data);
        } else {
            $this->redis->set($fullKey, $data);
        }
    }

    private function serializeLifecycle(CachedValueLifecycle $cachedValueLifecycle) : array
    {
        return [
            'createdAt'      => $cachedValueLifecycle->createdAt->toUnixTime(),
            'lastAccessedAt' => $cachedValueLifecycle->lastAccessedAt->toUnixTime(),
            'expiresAt'      => $cachedValueLifecycle->expiresAt->toUnixTime(),
            'refreshedAt'    => $cachedValueLifecycle->refreshedAt->toUnixTime(),
            'hitCount'       => $cachedValueLifecycle->hitCount,
            'refreshCount'   => $cachedValueLifecycle->refreshCount,
        ];
    }

    /**
     * @throws RedisClusterException
     */
    #[Override]
    public function clear() : void
    {
        $this->ensureConnected();

        $iterator = null;
        $pattern  = $this->prefix . '*';

        while ( true ) {
            $keys = $this->redis->scan($iterator, $pattern, 100);

            if ($keys === false) {
                break;
            }

            if (! empty($keys)) {
                $this->redis->del($keys);
            }

            if ($iterator === 0) {
                break;
            }
        }
    }

    /**
     * @throws RedisClusterException
     */
    #[Override]
    public function exists(CacheKey $cacheKey) : bool
    {
        $this->ensureConnected();

        $fullKey = $this->prefix . $cacheKey->fullKey();

        return (bool) $this->redis->exists($fullKey);
    }

    /**
     * @throws RedisClusterException
     */
    public function increment(string $key, int $value = 1) : int
    {
        $this->ensureConnected();

        $cacheKey = CacheKey::create(key: $key);
        $fullKey  = $this->prefix . $cacheKey->fullKey();

        return (int) $this->redis->incrBy($fullKey, $value);
    }

    /**
     * @throws RedisClusterException
     */
    public function decrement(string $key, int $value = 1) : int
    {
        $this->ensureConnected();

        $cacheKey = CacheKey::create(key: $key);
        $fullKey  = $this->prefix . $cacheKey->fullKey();

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
