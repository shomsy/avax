<?php

declare(strict_types=1);

namespace Avax\DataLayer\AccelerateDataReads;

final readonly class ReadAccelerationPlan
{
    public function __construct(
        public ChooseDataIndexResult $index,
        public UseReadCacheResult    $cache,
        public BloomFilterPlan       $bloom,
        public ChooseReadProjection  $projection,
        public float                 $estimatedSpeedup
    ) {}

    public function describeResponsibility() : string
    {
        return 'provides a complete read acceleration plan combining indexes, cache, bloom filters, and projections.';
    }

    public function toMetadata() : array
    {
        return [
            'index_enabled'        => $this->index->isUseful(),
            'cache_enabled'        => $this->cache->willCache(),
            'bloom_enabled'        => $this->bloom->willUse,
            'projection_coverable' => $this->projection->coverable,
            'estimated_speedup'    => $this->estimatedSpeedup,
        ];
    }

    public function isUseful() : bool
    {
        return $this->index->isUseful()
            || $this->cache->willCache()
            || $this->bloom->willUse
            || $this->projection->coverable;
    }
}