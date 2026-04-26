<?php

declare(strict_types=1);

namespace Avax\DataLayer\DescribeStorageBehavior;

use InvalidArgumentException;

enum ReadPathStrategy: string
{
    case POINT      = 'point';
    case RANGE      = 'range';
    case FULL_SCAN  = 'full_scan';
    case INDEX_SCAN = 'index_scan';
}

enum CacheMode: string
{
    case NONE         = 'none';
    case CACHE        = 'cache';
    case READ_REPLICA = 'read_replica';
}

final readonly class DescribeReadPath
{
    public function __construct(
        public ReadPathStrategy $strategy,
        public CacheMode        $cacheMode,
        public int              $prefetchSize,
        public bool             $parallelRead
    )
    {
        if ($this->prefetchSize < 0) {
            throw new InvalidArgumentException(message: 'Prefetch size must be non-negative.');
        }
    }

    public static function pointWithCache() : self
    {
        return new self(strategy: ReadPathStrategy::POINT, cacheMode: CacheMode::CACHE, prefetchSize: 100, parallelRead: false);
    }

    public static function indexScanWithPrefetch(int $prefetchSize = 1000) : self
    {
        return new self(strategy: ReadPathStrategy::INDEX_SCAN, cacheMode: CacheMode::CACHE, prefetchSize: $prefetchSize, parallelRead: true);
    }

    public function describeResponsibility() : string
    {
        return 'describes read path strategy, cache mode, prefetch size, and parallelism.';
    }

    public function shouldPrefetch() : bool
    {
        return $this->prefetchSize > 0;
    }

    public function toMetadata() : array
    {
        return [
            'strategy'      => $this->strategy->value,
            'cache_mode'    => $this->cacheMode->value,
            'prefetch_size' => $this->prefetchSize,
            'parallel_read' => $this->parallelRead,
        ];
    }
}