<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\CachedValues\CachedValueLifecycle;
use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Foundation\Serialization\CacheSerializer;
use Avax\Components\Application\Cache\System\Foundation\Serialization\JsonCacheSerializer;
use Avax\Components\Application\Cache\System\Foundation\Serialization\SerializedCachePayload;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;
use JsonException;
use Override;
use RuntimeException;
use SensitiveParameter;
use Throwable;

final class RedisCacheStore implements CacheStore
{
    private const int DEFAULT_PORT = 6379;

    private const float DEFAULT_TIMEOUT = 5.0;

    private const string DEFAULT_PREFIX = 'avax_cache:';

    private object $redis;

    public function __construct(
        private readonly Clock           $clock,
        private readonly CacheSerializer $cacheSerializer,
        private readonly string $host = '127.0.0.1',
        private readonly int $port = self::DEFAULT_PORT,
        #[SensitiveParameter]
        private readonly ?string $connectionSecret = null,
        private readonly int $database = 0,
        private readonly float $timeout = self::DEFAULT_TIMEOUT,
        private readonly string $prefix = self::DEFAULT_PREFIX,
    ) {
        if (! class_exists('Redis')) {
            throw new RuntimeException('Redis cache store requires the redis PHP extension.');
        }

        /** @var class-string $redisClass */
        $redisClass = 'Redis';
        $this->redis = new $redisClass();

        if ($this->callRedis('connect', $this->host, $this->port, $this->timeout) !== true) {
            throw new RuntimeException(sprintf('Cannot connect to Redis at %s:%d', $this->host, $this->port));
        }

        if ($this->connectionSecret !== null && $this->callRedis('auth', $this->connectionSecret) !== true) {
            throw new RuntimeException('Redis authentication failed.');
        }

        if ($this->database > 0 && $this->callRedis('select', $this->database) !== true) {
            throw new RuntimeException(sprintf('Cannot select Redis database %d.', $this->database));
        }
    }

    #[Override]
    public function read(CacheKey $cacheKey, Clock $clock): CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        $data = $this->callRedis('get', $this->prefixedKey(cacheKey: $cacheKey));

        if (! is_string($data) || $data === '') {
            return new CacheStoreRecordWasMissing(cacheKey: $cacheKey);
        }

        try {
            $decoded = json_decode($data, associative: true, depth: 512, flags: JSON_THROW_ON_ERROR);

            if (! is_array($decoded)) {
                return new CacheStoreRecordWasMissing(cacheKey: $cacheKey);
            }

            $lifecycle = $this->deserializeLifecycle(data: $decoded, clock: $clock);

            if ($lifecycle->isExpired(clock: $clock)) {
                $this->forget(cacheKey: $cacheKey);

                return new CacheStoreRecordWasMissing(cacheKey: $cacheKey);
            }

            $payloadData = is_string($decoded['payload'] ?? null) ? $decoded['payload'] : '';
            $payloadFormat = is_string($decoded['format'] ?? null) ? $decoded['format'] : JsonCacheSerializer::FORMAT;
            $payloadTimestamp = is_int($decoded['payloadTimestamp'] ?? null) ? $decoded['payloadTimestamp'] : $clock->now()->seconds;
            $payloadChecksum = is_string($decoded['checksum'] ?? null) ? $decoded['checksum'] : null;

            $payload = new SerializedCachePayload(
                data: $payloadData,
                format: $payloadFormat,
                timestamp: Timestamp::fromUnixTime(timestamp: $payloadTimestamp),
                checksum: $payloadChecksum,
            );

            $record = new StoredCacheRecord(
                value: $this->cacheSerializer->unserialize(serializedCachePayload: $payload),
                cachedValueLifecycle: $lifecycle->withAccessed(clock: $clock),
                serializedData: $payloadData,
                format: $payloadFormat,
            );

            return new CacheStoreRecordWasFound(
                cacheKey: $cacheKey,
                storedCacheRecord: $record,
                clock: $clock,
            );
        } catch (Throwable) {
            return new CacheStoreRecordWasMissing(cacheKey: $cacheKey);
        }
    }

    /**
     * @throws JsonException
     */
    #[Override]
    public function write(CacheKey $cacheKey, StoredCacheRecord $storedCacheRecord): void
    {
        $payload = $this->cacheSerializer->serialize(value: $storedCacheRecord->value);

        $data = [
            'payload' => $payload->data,
            'format' => $payload->format,
            'payloadTimestamp' => $payload->timestamp->seconds,
            'checksum' => $payload->checksum,
            'createdAt' => $storedCacheRecord->cachedValueLifecycle->createdAt->seconds,
            'lastAccessedAt' => $storedCacheRecord->cachedValueLifecycle->lastAccessedAt->seconds,
            'expiresAt' => $storedCacheRecord->cachedValueLifecycle->expiresAt->seconds,
            'refreshedAt' => $storedCacheRecord->cachedValueLifecycle->refreshedAt->seconds,
            'hitCount' => $storedCacheRecord->cachedValueLifecycle->hitCount,
            'refreshCount' => $storedCacheRecord->cachedValueLifecycle->refreshCount,
        ];

        $ttl = $storedCacheRecord->cachedValueLifecycle->expiresAt->seconds - $this->clock->now()->seconds;
        $content = json_encode($data, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);

        if ($ttl > 0) {
            $this->callRedis('setex', $this->prefixedKey(cacheKey: $cacheKey), $ttl, $content);

            return;
        }

        $this->callRedis('set', $this->prefixedKey(cacheKey: $cacheKey), $content);
    }

    #[Override]
    public function forget(CacheKey $cacheKey): void
    {
        $this->callRedis('del', $this->prefixedKey(cacheKey: $cacheKey));
    }

    #[Override]
    public function clear(): void
    {
        $keys = $this->callRedis('keys', $this->prefix.'*');

        if (! is_array($keys)) {
            return;
        }

        foreach ($keys as $key) {
            if (is_string($key)) {
                $this->callRedis('del', $key);
            }
        }
    }

    #[Override]
    public function exists(CacheKey $cacheKey): bool
    {
        $exists = $this->callRedis('exists', $this->prefixedKey(cacheKey: $cacheKey));

        return is_int($exists) ? $exists > 0 : $exists === true;
    }

    private function prefixedKey(CacheKey $cacheKey): string
    {
        return $this->prefix.$cacheKey->fullKey();
    }

    private function callRedis(string $method, mixed ...$arguments): mixed
    {
        if (! method_exists($this->redis, $method)) {
            throw new RuntimeException(sprintf('Redis method "%s" is not available.', $method));
        }

        return $this->redis->{$method}(...$arguments);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function deserializeLifecycle(array $data, Clock $clock): CachedValueLifecycle
    {
        $now = $clock->now();
        $createdAt = is_int($data['createdAt'] ?? null) ? $data['createdAt'] : $now->seconds;
        $lastAccessedAt = is_int($data['lastAccessedAt'] ?? null) ? $data['lastAccessedAt'] : $now->seconds;
        $expiresAt = is_int($data['expiresAt'] ?? null) ? $data['expiresAt'] : $now->seconds;
        $refreshedAt = is_int($data['refreshedAt'] ?? null) ? $data['refreshedAt'] : $now->seconds;
        $hitCount = is_int($data['hitCount'] ?? null) ? $data['hitCount'] : 0;
        $refreshCount = is_int($data['refreshCount'] ?? null) ? $data['refreshCount'] : 0;

        return new CachedValueLifecycle(
            createdAt: Timestamp::fromUnixTime(timestamp: $createdAt),
            lastAccessedAt: Timestamp::fromUnixTime(timestamp: $lastAccessedAt),
            expiresAt: Timestamp::fromUnixTime(timestamp: $expiresAt),
            refreshedAt: Timestamp::fromUnixTime(timestamp: $refreshedAt),
            hitCount: $hitCount,
            refreshCount: $refreshCount,
        );
    }
}
