<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Observability\ObserveCache;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Override;
use Stringable;

final readonly class CacheOperation implements Stringable
{
    public CacheKey $cacheKey;

    public function __construct(
        public string      $operation,
        public CacheKey $cacheKey,
        public float       $timestamp,
        public int|null    $ttlSeconds = null,
        public int|null    $durationMicroseconds = null,
        public string|null $storeName = null,
        public string|null $tier = null,
    )
    {
        $this->cacheKey = $cacheKey;
    }

    public static function read(
        CacheKey $cacheKey,
        float    $timestamp,
        int|null    $durationMicroseconds = null,
        string|null $storeName = null,
    ) : self
    {
        return new self(
            operation           : 'read',
            key                 : $cacheKey,
            timestamp           : $timestamp,
            durationMicroseconds: $durationMicroseconds,
            storeName           : $storeName,
        );
    }

    public static function write(
        CacheKey $cacheKey,
        float    $timestamp,
        int      $ttlSeconds,
        int|null    $durationMicroseconds = null,
        string|null $storeName = null,
    ) : self
    {
        return new self(
            operation           : 'write',
            key                 : $cacheKey,
            timestamp           : $timestamp,
            ttlSeconds          : $ttlSeconds,
            durationMicroseconds: $durationMicroseconds,
            storeName           : $storeName,
        );
    }

    public static function delete(
        CacheKey $cacheKey,
        float    $timestamp,
        int|null    $durationMicroseconds = null,
        string|null $storeName = null,
    ) : self
    {
        return new self(
            operation           : 'delete',
            key                 : $cacheKey,
            timestamp           : $timestamp,
            durationMicroseconds: $durationMicroseconds,
            storeName           : $storeName,
        );
    }

    public static function hit(
        CacheKey $cacheKey,
        float    $timestamp,
        int|null    $durationMicroseconds = null,
        string|null $storeName = null,
    ) : self
    {
        return new self(
            operation           : 'hit',
            key                 : $cacheKey,
            timestamp           : $timestamp,
            durationMicroseconds: $durationMicroseconds,
            storeName           : $storeName,
        );
    }

    public static function miss(
        CacheKey $cacheKey,
        float    $timestamp,
        int|null    $durationMicroseconds = null,
        string|null $storeName = null,
    ) : self
    {
        return new self(
            operation           : 'miss',
            key                 : $cacheKey,
            timestamp           : $timestamp,
            durationMicroseconds: $durationMicroseconds,
            storeName           : $storeName,
        );
    }

    public static function eviction(
        CacheKey $cacheKey,
        float    $timestamp,
        string   $reason,
        string|null $storeName = null,
    ) : self
    {
        return new self(
            operation: 'eviction',
            key      : $cacheKey,
            timestamp: $timestamp,
            storeName: $storeName,
        );
    }

    public static function invalidation(
        CacheKey $cacheKey,
        float    $timestamp,
        string   $reason,
        string|null $storeName = null,
    ) : self
    {
        return new self(
            operation: 'invalidation',
            key      : $cacheKey,
            timestamp: $timestamp,
            storeName: $storeName,
        );
    }

    public static function refresh(
        CacheKey $cacheKey,
        float    $timestamp,
        int|null    $durationMicroseconds = null,
        string|null $storeName = null,
    ) : self
    {
        return new self(
            operation           : 'refresh',
            key                 : $cacheKey,
            timestamp           : $timestamp,
            durationMicroseconds: $durationMicroseconds,
            storeName           : $storeName,
        );
    }

    public static function sourceFailure(
        CacheKey $cacheKey,
        float    $timestamp,
        string   $error,
        string|null $storeName = null,
    ) : self
    {
        return new self(
            operation: 'source_failure',
            key      : $cacheKey,
            timestamp: $timestamp,
            storeName: $storeName,
        );
    }

    public static function storeFailure(
        CacheKey $cacheKey,
        float    $timestamp,
        string   $error,
        string|null $storeName = null,
    ) : self
    {
        return new self(
            operation: 'store_failure',
            key      : $cacheKey,
            timestamp: $timestamp,
            storeName: $storeName,
        );
    }

    public function isRead() : bool
    {
        return $this->operation === 'read';
    }

    public function isWrite() : bool
    {
        return $this->operation === 'write';
    }

    public function isDelete() : bool
    {
        return $this->operation === 'delete';
    }

    #[Override]
    public function __toString() : string
    {
        return sprintf(
            '%s:%s at %s',
            $this->operation,
            $this->cacheKey->fullKey(),
            date('Y-m-d H:i:s', (int) $this->timestamp),
        );
    }
}
