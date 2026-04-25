<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\StoreCachedValues;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Capabilities\ManageCacheLifecycle\CachedValueLifecycle;
use Avax\Cache\System\Foundation\Serialization\JsonCacheSerializer;
use Avax\Cache\System\Foundation\Time\Clock;
use Avax\Cache\System\Foundation\Time\SystemClock;
use Avax\Cache\System\Foundation\Time\Timestamp;
use Throwable;

final class FileCacheStore implements CacheStore
{
    private const FILE_EXTENSION = '.cache';
    private const WRITE_MODE     = 0644;
    private const LOCK_MODE      = LOCK_EX;

    private string              $basePath;
    private JsonCacheSerializer $serializer;

    public function __construct(
        string               $basePath,
        private Clock        $clock = new SystemClock(),
        ?JsonCacheSerializer $serializer = null
    )
    {
        $this->basePath   = rtrim($basePath, '/\\');
        $this->serializer = $serializer ?? new JsonCacheSerializer($this->clock);

        $this->ensureDirectoryExists();
    }

    private function ensureDirectoryExists() : void
    {
        if (! is_dir($this->basePath)) {
            mkdir($this->basePath, 0755, recursive: true);
        }
    }

    public function write(CacheKey $key, StoredCacheRecord $record) : void
    {
        $filePath = $this->getFilePath($key);
        $this->ensureDirectoryExistsForKey($filePath);

        $data = [
            'value'          => $record->value,
            'lifecycle'      => $this->serializeLifecycle($record->lifecycle),
            'serializedData' => $record->serializedData,
            'format'         => $record->format,
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
        $files = glob($this->basePath . '/*' . self::FILE_EXTENSION);

        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }

    public function exists(CacheKey $key) : bool
    {
        $filePath = $this->getFilePath($key);

        if (! file_exists($filePath)) {
            return false;
        }

        $result = $this->read($key, $this->clock);

        return $result instanceof CacheStoreRecordWasFound;
    }

    public function read(CacheKey $key, Clock $clock) : CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        $filePath = $this->getFilePath($key);

        if (! file_exists($filePath)) {
            return new CacheStoreRecordWasMissing($key);
        }

        $content = file_get_contents($filePath);

        if ($content === false || $content === '') {
            $this->forget($key);

            return new CacheStoreRecordWasMissing($key);
        }

        try {
            $data = json_decode($content, associative: true, depth: 512);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->forget($key);

                return new CacheStoreRecordWasMissing($key);
            }

            $lifecycle = $this->deserializeLifecycle($data['lifecycle'] ?? [], $clock);

            if ($lifecycle->isExpired($clock)) {
                $this->forget($key);

                return new CacheStoreRecordWasMissing($key);
            }

            $record = new StoredCacheRecord(
                value         : $data['value'] ?? null,
                lifecycle     : $lifecycle,
                serializedData: $data['serializedData'] ?? null,
                format        : $data['format'] ?? null
            );

            $updatedLifecycle = $lifecycle->withAccessed($clock);
            $record           = new StoredCacheRecord(
                value         : $record->value,
                lifecycle     : $updatedLifecycle,
                serializedData: $record->serializedData,
                format        : $record->format
            );

            return new CacheStoreRecordWasFound($key, $record, $clock);
        } catch (Throwable) {
            $this->forget($key);

            return new CacheStoreRecordWasMissing($key);
        }
    }

    public function forget(CacheKey $key) : void
    {
        $filePath = $this->getFilePath($key);

        if (file_exists($filePath)) {
            @unlink($filePath);
        }
    }

    private function deserializeLifecycle(array $data, Clock $clock) : CachedValueLifecycle
    {
        $now = $clock->now();

        return new CachedValueLifecycle(
            createdAt     : Timestamp::fromUnixTime($data['createdAt'] ?? $now->seconds),
            lastAccessedAt: Timestamp::fromUnixTime($data['lastAccessedAt'] ?? $now->seconds),
            expiresAt     : Timestamp::fromUnixTime($data['expiresAt'] ?? $now->seconds),
            refreshedAt   : Timestamp::fromUnixTime($data['refreshedAt'] ?? $now->seconds),
            hitCount      : $data['hitCount'] ?? 0,
            refreshCount  : $data['refreshCount'] ?? 0
        );
    }
}