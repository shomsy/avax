<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Storage\StoreCachedValues;

use Avax\Cache\System\Capabilities\Lifecycle\CachedValues\CachedValueLifecycle;
use Avax\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Foundation\Serialization\CacheSerializer;
use Avax\Cache\System\Foundation\Serialization\JsonCacheSerializer;
use Avax\Cache\System\Foundation\Serialization\SerializedCachePayload;
use Avax\Cache\System\Foundation\Time\Clock;
use Avax\Cache\System\Foundation\Time\SystemClock;
use Avax\Cache\System\Foundation\Time\Timestamp;
use JsonException;
use Redis;
use RedisArray;
use RedisCluster;
use RedisClusterException;
use SensitiveParameter;

final class RedisCacheStore implements CacheStore
{
    private const DEFAULT_PORT    = 6379;
    private const DEFAULT_TIMEOUT = 5.0;
    private const DEFAULT_PREFIX  = 'avax_cache:';

    private Redis|RedisArray|RedisCluster|null $redis     = null;
    private bool                               $connected = false;
    private CacheSerializer $serializer;

    public function __construct(
        private readonly string                            $host = '127.0.0.1',
        private readonly int                               $port = self::DEFAULT_PORT,
        #[SensitiveParameter] private readonly string|null $connectionSecret = null,
        private readonly int                               $database = 0,
        private readonly float                             $timeout = self::DEFAULT_TIMEOUT,
        private readonly string                            $prefix = self::DEFAULT_PREFIX,
        private readonly Clock                             $clock = new SystemClock(),
        CacheSerializer|null                               $serializer = null
    )
    {
        $this->serializer = $serializer ?? new JsonCacheSerializer(clock: $this->clock);
    }

    /**
     * @throws RedisClusterException
     */
    public function read(CacheKey $key, Clock $clock) : CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        $this->ensureConnected();

        $fullKey = $this->prefix . $key->fullKey();
        $data = $this->redis->get($fullKey);

        if ($data === false || $data === null) {
            return new CacheStoreRecordWasMissing(key: $key);
        }

        $decoded = json_decode($data, associative: true);

        if ($decoded === null) {
            return new CacheStoreRecordWasMissing(key: $key);
        }

        $lifecycle = $this->deserializeLifecycle(data: $decoded['lifecycle'] ?? [], clock: $clock);

        if ($lifecycle->isExpired(clock: $clock)) {
            $this->forget(key: $key);

            return new CacheStoreRecordWasMissing(key: $key);
        }

        $valuePayload = SerializedCachePayload::create(
            data  : $decoded['value'] ?? '',
            format: $decoded['format'] ?? 'json',
            clock : $clock
        );

        $value = $this->serializer->unserialize(payload: $valuePayload);

        $record = new StoredCacheRecord(
            value    : $value,
            lifecycle: $lifecycle
        );

        return new CacheStoreRecordWasFound(key: $key, record: $record, clock: $clock);
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
            refreshCount  : $data['refreshCount'] ?? 0
        );
    }

    /**
     * @throws RedisClusterException
     */
    public function forget(CacheKey $key) : void
    {
        $this->ensureConnected();

        $fullKey = $this->prefix . $key->fullKey();
        $this->redis->del($fullKey);
    }

    /**
     * @throws JsonException
     * @throws RedisClusterException
     */
    public function write(CacheKey $key, StoredCacheRecord $record) : void
    {
        $this->ensureConnected();

        $fullKey = $this->prefix . $key->fullKey();

        $serializedPayload = $this->serializer->serialize(value: $record->value);

        $data = json_encode([
                                'value'     => $serializedPayload->data,
                                'format'    => $serializedPayload->format,
                                'lifecycle' => $this->serializeLifecycle(lifecycle: $record->lifecycle),
                            ], JSON_THROW_ON_ERROR);

        $ttl = $record->lifecycle->timeToLive(clock: $this->clock);

        if ($ttl > 0 && $ttl < PHP_INT_MAX) {
            $this->redis->setex($fullKey, (int) $ttl, $data);
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

    /**
     * @throws RedisClusterException
     */
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
    public function exists(CacheKey $key) : bool
    {
        $this->ensureConnected();

        $fullKey = $this->prefix . $key->fullKey();

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