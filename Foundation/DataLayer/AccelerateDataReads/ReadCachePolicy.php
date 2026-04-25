<?php

declare(strict_types=1);

namespace Avax\DataLayer\AccelerateDataReads;

use InvalidArgumentException;

enum ReadCacheType: string
{
    case QUERY  = 'query';
    case RESULT = 'result';
    case FULL   = 'full';
}

enum CacheEvictionPolicy: string
{
    case LRU = 'lru';
    case LFU = 'lfu';
    case TTL = 'ttl';
}

final readonly class ReadCachePolicy
{
    public function __construct(
        public ReadCacheType       $type,
        public CacheEvictionPolicy $evictionPolicy,
        public int                 $maxSizeBytes,
        public int                 $ttlSeconds,
        public float               $hitRateTarget
    )
    {
        if ($this->maxSizeBytes < 0) {
            throw new InvalidArgumentException(message: 'Max size must be non-negative.');
        }
        if ($this->ttlSeconds < 0) {
            throw new InvalidArgumentException(message: 'TTL must be non-negative.');
        }
    }

    public function describeResponsibility() : string
    {
        return 'records read cache policy including type, eviction, size limits, TTL, and hit rate target.';
    }

    public static function standard() : self
    {
        return new self(type: ReadCacheType::RESULT, evictionPolicy: CacheEvictionPolicy::LRU, maxSizeBytes: 1073741824, ttlSeconds: 300, hitRateTarget: 0.8);
    }

    public static function aggressive() : self
    {
        return new self(type: ReadCacheType::FULL, evictionPolicy: CacheEvictionPolicy::LFU, maxSizeBytes: 2147483648, ttlSeconds: 3600, hitRateTarget: 0.9);
    }

    public function shouldEvict(float $currentSizeBytes, int $accessCount) : bool
    {
        if ($currentSizeBytes >= $this->maxSizeBytes) {
            return true;
        }
        if ($this->evictionPolicy === CacheEvictionPolicy::TTL) {
            return true;
        }

        return false;
    }

    public function toMetadata() : array
    {
        return [
            'type'            => $this->type->value,
            'eviction_policy' => $this->evictionPolicy->value,
            'max_size_bytes'  => $this->maxSizeBytes,
            'ttl_seconds'     => $this->ttlSeconds,
            'hit_rate_target' => $this->hitRateTarget,
        ];
    }
}