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
use Override;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Throwable;

final readonly class FileCacheStore implements CacheStore
{
    private const string FILE_EXTENSION = '.cache';

    private const int WRITE_MODE = 0o644;

    private JsonCacheSerializer $jsonCacheSerializer;

    public function __construct(
        private string $basePath,
        private Clock $clock = new SystemClock(),
        ?JsonCacheSerializer $jsonCacheSerializer = null,
    ) {
        $this->jsonCacheSerializer = $jsonCacheSerializer ?? new JsonCacheSerializer(clock: $this->clock);

        $this->ensureDirectoryExists();
    }

    private function ensureDirectoryExists(): void
    {
        if (! is_dir($this->basePath)) {
            mkdir($this->basePath, 0o755, recursive: true);
        }
    }

    /**
     * @throws JsonException
     */
    #[Override]
    public function write(CacheKey $cacheKey, StoredCacheRecord $storedCacheRecord): void
    {
        $filePath = $this->getFilePath(cacheKey: $cacheKey);
        $this->ensureDirectoryExistsForKey(filePath: $filePath);

        $serializedCachePayload = $this->jsonCacheSerializer->serialize(value: $storedCacheRecord->value);

        $data = [
            'value' => $serializedCachePayload->data,
            'format' => $serializedCachePayload->format,
            'lifecycle' => $this->serializeLifecycle(cachedValueLifecycle: $storedCacheRecord->cachedValueLifecycle),
            'serializedData' => $storedCacheRecord->serializedData,
        ];

        $content = json_encode($data, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);

        $tempPath = $filePath.'.tmp.'.uniqid(more_entropy: true);

        $result = file_put_contents(
            filename: $tempPath,
            data    : $content,
            flags   : LOCK_EX,
        );

        if ($result === false) {
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }

            throw new StoreCachedValueFailed(
                message: sprintf('Failed to write cache file for key "%s"', $cacheKey->fullKey()),
                key    : $cacheKey->fullKey(),
            );
        }

        if (! rename($tempPath, $filePath)) {
            @unlink($tempPath);

            throw new StoreCachedValueFailed(
                message: sprintf('Failed to finalize cache file for key "%s"', $cacheKey->fullKey()),
                key    : $cacheKey->fullKey(),
            );
        }

        chmod($filePath, self::WRITE_MODE);
    }

    private function getFilePath(CacheKey $cacheKey): string
    {
        $keyHash = hash('xxh128', $cacheKey->fullKey());
        $subDir = substr($keyHash, 0, 2);

        return $this->basePath.'/'.$subDir.'/'.$keyHash.self::FILE_EXTENSION;
    }

    private function ensureDirectoryExistsForKey(string $filePath): void
    {
        $dir = dirname($filePath);

        if (! is_dir($dir)) {
            mkdir($dir, 0o755, recursive: true);
        }
    }

    /**
     * @return array{
     *     createdAt: int,
     *     lastAccessedAt: int,
     *     expiresAt: int,
     *     refreshedAt: int,
     *     hitCount: int,
     *     refreshCount: int
     * }
     */
    private function serializeLifecycle(CachedValueLifecycle $cachedValueLifecycle): array
    {
        return [
            'createdAt' => $cachedValueLifecycle->createdAt->toUnixTime(),
            'lastAccessedAt' => $cachedValueLifecycle->lastAccessedAt->toUnixTime(),
            'expiresAt' => $cachedValueLifecycle->expiresAt->toUnixTime(),
            'refreshedAt' => $cachedValueLifecycle->refreshedAt->toUnixTime(),
            'hitCount' => $cachedValueLifecycle->hitCount,
            'refreshCount' => $cachedValueLifecycle->refreshCount,
        ];
    }

    #[Override]
    public function clear(): void
    {
        $this->recursiveDelete(directory: $this->basePath);
        $this->ensureDirectoryExists();
    }

    private function recursiveDelete(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($items as $item) {
            if (! $item instanceof SplFileInfo) {
                continue;
            }

            $path = $item->getRealPath();

            if ($path === false) {
                continue;
            }

            if ($item->isDir()) {
                @rmdir($path);
            } else {
                @unlink($path);
            }
        }
    }

    #[Override]
    public function exists(CacheKey $cacheKey): bool
    {
        $filePath = $this->getFilePath(cacheKey: $cacheKey);

        if (! file_exists($filePath)) {
            return false;
        }

        $result = $this->read(cacheKey: $cacheKey, clock: $this->clock);

        return $result instanceof CacheStoreRecordWasFound;
    }

    #[Override]
    public function read(CacheKey $cacheKey, Clock $clock): CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        $filePath = $this->getFilePath(cacheKey: $cacheKey);

        if (! file_exists($filePath)) {
            return new CacheStoreRecordWasMissing(cacheKey: $cacheKey);
        }

        $content = file_get_contents($filePath);

        if ($content === false || $content === '') {
            $this->forget(cacheKey: $cacheKey);

            return new CacheStoreRecordWasMissing(cacheKey: $cacheKey);
        }

        try {
            $data = json_decode($content, associative: true, depth: 512);

            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($data)) {
                $this->forget(cacheKey: $cacheKey);

                return new CacheStoreRecordWasMissing(cacheKey: $cacheKey);
            }

            $lifecycleData = is_array($data['lifecycle'] ?? null) ? $data['lifecycle'] : [];
            $lifecycle = $this->deserializeLifecycle(data: $lifecycleData, clock: $clock);

            if ($lifecycle->isExpired(clock: $clock)) {
                $this->forget(cacheKey: $cacheKey);

                return new CacheStoreRecordWasMissing(cacheKey: $cacheKey);
            }

            $serializedValue = is_string($data['value'] ?? null) ? $data['value'] : '';
            $format = is_string($data['format'] ?? null) ? $data['format'] : 'json';

            $valuePayload = SerializedCachePayload::create(
                data  : $serializedValue,
                format: $format,
                clock : $clock,
            );

            $value = $this->jsonCacheSerializer->unserialize(serializedCachePayload: $valuePayload);

            $serializedData = is_string($data['serializedData'] ?? null) ? $data['serializedData'] : null;

            $record = new StoredCacheRecord(
                value               : $value,
                serializedData      : $serializedData,
                format              : $format,
                cachedValueLifecycle: $lifecycle,
            );

            $updatedLifecycle = $lifecycle->withAccessed(clock: $clock);
            $record = new StoredCacheRecord(
                value               : $record->value,
                serializedData      : $record->serializedData,
                format              : $record->format,
                cachedValueLifecycle: $updatedLifecycle,
            );

            return new CacheStoreRecordWasFound(
                cacheKey: $cacheKey,
                storedCacheRecord: $record,
                clock: $clock,
            );
        } catch (Throwable) {
            $this->forget(cacheKey: $cacheKey);

            return new CacheStoreRecordWasMissing(cacheKey: $cacheKey);
        }
    }

    #[Override]
    public function forget(CacheKey $cacheKey): void
    {
        $filePath = $this->getFilePath(cacheKey: $cacheKey);

        if (file_exists($filePath)) {
            @unlink($filePath);
        }
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
            createdAt     : Timestamp::fromUnixTime(timestamp: $createdAt),
            lastAccessedAt: Timestamp::fromUnixTime(timestamp: $lastAccessedAt),
            expiresAt     : Timestamp::fromUnixTime(timestamp: $expiresAt),
            refreshedAt   : Timestamp::fromUnixTime(timestamp: $refreshedAt),
            hitCount      : $hitCount,
            refreshCount  : $refreshCount,
        );
    }
}
