<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\CachedValues\CachedValueLifecycle;
use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Foundation\Serialization\JsonCacheSerializer;
use Avax\Components\Application\Cache\System\Foundation\Serialization\SerializedCachePayload;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;
use JsonException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;

final class FileCacheStore implements CacheStore
{
    private const FILE_EXTENSION = '.cache';
    private const WRITE_MODE     = 0644;
    private const LOCK_MODE      = LOCK_EX;

    private JsonCacheSerializer $serializer;

    public function __construct(
        private readonly string  $basePath,
        private readonly Clock   $clock = new SystemClock(),
        JsonCacheSerializer|null $serializer = null
    )
    {
        $this->serializer = $serializer ?? new JsonCacheSerializer(clock: $this->clock);

        $this->ensureDirectoryExists();
    }

    private function ensureDirectoryExists() : void
    {
        if (! is_dir($this->basePath)) {
            mkdir($this->basePath, 0755, recursive: true);
        }
    }

    /**
     * @throws JsonException
     */
    public function write(CacheKey $key, StoredCacheRecord $record) : void
    {
        $filePath = $this->getFilePath(key: $key);
        $this->ensureDirectoryExistsForKey(filePath: $filePath);

        $serializedPayload = $this->serializer->serialize(value: $record->value);

        $data = [
            'value'          => $serializedPayload->data,
            'format'         => $serializedPayload->format,
            'lifecycle'      => $this->serializeLifecycle(lifecycle: $record->lifecycle),
            'serializedData' => $record->serializedData,
        ];

        $content = json_encode($data, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);

        $tempPath = $filePath . '.tmp.' . uniqid(more_entropy: true);

        $result = file_put_contents(
            filename: $tempPath,
            data    : $content,
            flags   : LOCK_EX
        );

        if ($result === false) {
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }

            throw new StoreCachedValueFailed(
                message: sprintf('Failed to write cache file for key "%s"', $key->fullKey()),
                key    : $key->fullKey()
            );
        }

        if (! rename($tempPath, $filePath)) {
            @unlink($tempPath);

            throw new StoreCachedValueFailed(
                message: sprintf('Failed to finalize cache file for key "%s"', $key->fullKey()),
                key    : $key->fullKey()
            );
        }

        chmod($filePath, self::WRITE_MODE);
    }

    private function getFilePath(CacheKey $key) : string
    {
        $keyHash = hash('xxh128', $key->fullKey());
        $subDir  = substr($keyHash, 0, 2);

        return $this->basePath . '/' . $subDir . '/' . $keyHash . self::FILE_EXTENSION;
    }

    private function ensureDirectoryExistsForKey(string $filePath) : void
    {
        $dir = dirname($filePath);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, recursive: true);
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
        $this->recursiveDelete(directory: $this->basePath);
        $this->ensureDirectoryExists();
    }

    private function recursiveDelete(string $directory) : void
    {
        if (! is_dir($directory)) {
            return;
        }

        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            if ($item->isDir()) {
                @rmdir($item->getRealPath());
            } else {
                @unlink($item->getRealPath());
            }
        }
    }

    public function exists(CacheKey $key) : bool
    {
        $filePath = $this->getFilePath(key: $key);

        if (! file_exists($filePath)) {
            return false;
        }

        $result = $this->read(key: $key, clock: $this->clock);

        return $result instanceof CacheStoreRecordWasFound;
    }

    public function read(CacheKey $key, Clock $clock) : CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        $filePath = $this->getFilePath(key: $key);

        if (! file_exists($filePath)) {
            return new CacheStoreRecordWasMissing(key: $key);
        }

        $content = file_get_contents($filePath);

        if ($content === false || $content === '') {
            $this->forget(key: $key);

            return new CacheStoreRecordWasMissing(key: $key);
        }

        try {
            $data = json_decode($content, associative: true, depth: 512);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->forget(key: $key);

                return new CacheStoreRecordWasMissing(key: $key);
            }

            $lifecycle = $this->deserializeLifecycle(data: $data['lifecycle'] ?? [], clock: $clock);

            if ($lifecycle->isExpired(clock: $clock)) {
                $this->forget(key: $key);

                return new CacheStoreRecordWasMissing(key: $key);
            }

            $valuePayload = SerializedCachePayload::create(
                data  : $data['value'] ?? '',
                format: $data['format'] ?? 'json',
                clock : $clock
            );

            $value = $this->serializer->unserialize(payload: $valuePayload);

            $record = new StoredCacheRecord(
                value         : $value,
                lifecycle     : $lifecycle,
                serializedData: $data['serializedData'] ?? null,
                format        : $data['format'] ?? null
            );

            $updatedLifecycle = $lifecycle->withAccessed(clock: $clock);
            $record           = new StoredCacheRecord(
                value         : $record->value,
                lifecycle     : $updatedLifecycle,
                serializedData: $record->serializedData,
                format        : $record->format
            );

            return new CacheStoreRecordWasFound(key: $key, record: $record, clock: $clock);
        } catch (Throwable) {
            $this->forget(key: $key);

            return new CacheStoreRecordWasMissing(key: $key);
        }
    }

    public function forget(CacheKey $key) : void
    {
        $filePath = $this->getFilePath(key: $key);

        if (file_exists($filePath)) {
            @unlink($filePath);
        }
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
}