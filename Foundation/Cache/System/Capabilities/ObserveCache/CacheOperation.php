<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ObserveCache;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;
use Stringable;

final readonly class CacheOperation implements Stringable
{
    public function __construct(
        public string   $operation,
        public CacheKey $key,
        public float    $timestamp,
        public ?int     $ttlSeconds = null,
        public ?int     $durationMicroseconds = null,
        public ?string  $storeName = null,
        public ?string  $tier = null
    ) {}

    public static function read(
        CacheKey $key,
        float    $timestamp,
        ?int     $durationMicroseconds = null,
        ?string  $storeName = null
    ) : self
    {
        return new self(
            operation           : 'read',
            key                 : $key,
            timestamp           : $timestamp,
            durationMicroseconds: $durationMicroseconds,
            storeName           : $storeName
        );
    }

    public static function write(
        CacheKey $key,
        float    $timestamp,
        int      $ttlSeconds,
        ?int     $durationMicroseconds = null,
        ?string  $storeName = null
    ) : self
    {
        return new self(
            operation           : 'write',
            key                 : $key,
            timestamp           : $timestamp,
            ttlSeconds          : $ttlSeconds,
            durationMicroseconds: $durationMicroseconds,
            storeName           : $storeName
        );
    }

    public static function delete(
        CacheKey $key,
        float    $timestamp,
        ?int     $durationMicroseconds = null,
        ?string  $storeName = null
    ) : self
    {
        return new self(
            operation           : 'delete',
            key                 : $key,
            timestamp           : $timestamp,
            durationMicroseconds: $durationMicroseconds,
            storeName           : $storeName
        );
    }

    public static function hit(
        CacheKey $key,
        float    $timestamp,
        ?int     $durationMicroseconds = null,
        ?string  $storeName = null
    ) : self
    {
        return new self(
            operation           : 'hit',
            key                 : $key,
            timestamp           : $timestamp,
            durationMicroseconds: $durationMicroseconds,
            storeName           : $storeName
        );
    }

    public static function miss(
        CacheKey $key,
        float    $timestamp,
        ?int     $durationMicroseconds = null,
        ?string  $storeName = null
    ) : self
    {
        return new self(
            operation           : 'miss',
            key                 : $key,
            timestamp           : $timestamp,
            durationMicroseconds: $durationMicroseconds,
            storeName           : $storeName
        );
    }

    public static function eviction(
        CacheKey $key,
        float    $timestamp,
        string   $reason,
        ?string  $storeName = null
    ) : self
    {
        return new self(
            operation: 'eviction',
            key      : $key,
            timestamp: $timestamp,
            storeName: $storeName
        );
    }

    public static function invalidation(
        CacheKey $key,
        float    $timestamp,
        string   $reason,
        ?string  $storeName = null
    ) : self
    {
        return new self(
            operation: 'invalidation',
            key      : $key,
            timestamp: $timestamp,
            storeName: $storeName
        );
    }

    public static function refresh(
        CacheKey $key,
        float    $timestamp,
        ?int     $durationMicroseconds = null,
        ?string  $storeName = null
    ) : self
    {
        return new self(
            operation           : 'refresh',
            key                 : $key,
            timestamp           : $timestamp,
            durationMicroseconds: $durationMicroseconds,
            storeName           : $storeName
        );
    }

    public static function sourceFailure(
        CacheKey $key,
        float    $timestamp,
        string   $error,
        ?string  $storeName = null
    ) : self
    {
        return new self(
            operation: 'source_failure',
            key      : $key,
            timestamp: $timestamp,
            storeName: $storeName
        );
    }

    public static function storeFailure(
        CacheKey $key,
        float    $timestamp,
        string   $error,
        ?string  $storeName = null
    ) : self
    {
        return new self(
            operation: 'store_failure',
            key      : $key,
            timestamp: $timestamp,
            storeName: $storeName
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

    public function __toString() : string
    {
        return sprintf(
            '%s:%s at %s',
            $this->operation,
            $this->key->fullKey(),
            date('Y-m-d H:i:s', (int) $this->timestamp)
        );
    }
}