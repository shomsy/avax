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
use Avax\Components\Infrastructure\System\Capabilities\Cache\Driver;
use Avax\Components\Infrastructure\System\Capabilities\Cache\Redis;
use JsonException;
use Override;
use SensitiveParameter;

final class RedisCacheStore implements CacheStoreInterface
{
    private const int DEFAULT_PORT = 6379;

    private const float DEFAULT_TIMEOUT = 5.0;

    private const string DEFAULT_PREFIX = 'avax_cache:';

    private readonly CacheSerializer $cacheSerializer;

    private Driver $adapter;

    public function __construct(
        private readonly string $host = '127.0.0.1',
        private readonly int   $port = self::DEFAULT_PORT,
        #[SensitiveParameter]
        private readonly ?string $connectionSecret = null,
        private readonly int   $database = 0,
        private readonly float $timeout = self::DEFAULT_TIMEOUT,
        private readonly string $prefix = self::DEFAULT_PREFIX,
        private readonly Clock $clock = new SystemClock(),
        ?CacheSerializer        $cacheSerializer = null,
    )
    {
        $this->cacheSerializer = $cacheSerializer ?? new JsonCacheSerializer(clock: $this->clock);
        $this->adapter = new Redis(
            host: $this->host,
            port: $this->port,
            auth: $this->connectionSecret,
            database: $this->database,
            timeout: $this->timeout
        );
    }

    #[Override]
    public function read(CacheKey $cacheKey, Clock $clock) : CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        $fullKey = $this->prefix . $cacheKey->fullKey();
        $data    = $this->adapter->get($fullKey);

        if ($data === false || $data === null) {
            return new CacheStoreRecordWasMissing(key: $cacheKey);
        }

        $decoded = json_decode((string) $data, associative: true);

        if (! is_array($decoded)) {
             return new CacheStoreRecordWasMissing(key: $cacheKey);
        }

        $cachedValueLifecycle = $this->deserializeLifecycle(data: $decoded, clock: $clock);
        $serializedCachePayload = new SerializedCachePayload(payload: $decoded['payload'] ?? '');
        $value = $this->cacheSerializer->unserialize(payload: $serializedCachePayload);

        $storedCacheRecord = new StoredCacheRecord(
            value    : $value,
            lifecycle: $cachedValueLifecycle,
        );

        return new CacheStoreRecordWasFound(clock: $clock, key: $cacheKey, record: $storedCacheRecord);
    }

    #[Override]
    public function write(CacheKey $cacheKey, StoredCacheRecord $record, Clock $clock) : void
    {
        $fullKey = $this->prefix . $cacheKey->fullKey();
        
        $data = [
            'payload' => $this->cacheSerializer->serialize(value: $record->value)->payload,
            'createdAt' => $record->lifecycle->createdAt->seconds,
            'lastAccessedAt' => $record->lifecycle->lastAccessedAt->seconds,
            'expiresAt' => $record->lifecycle->expiresAt->seconds,
            'refreshedAt' => $record->lifecycle->refreshedAt->seconds,
            'hitCount' => $record->lifecycle->hitCount,
            'refreshCount' => $record->lifecycle->refreshCount,
        ];

        $ttl = $record->lifecycle->expiresAt->seconds - $clock->now()->seconds;
        
        $this->adapter->set($fullKey, json_encode($data), $ttl > 0 ? (int)$ttl : null);
    }

    #[Override]
    public function delete(CacheKey $cacheKey) : void
    {
        $fullKey = $this->prefix . $cacheKey->fullKey();
        $this->adapter->del($fullKey);
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
}
