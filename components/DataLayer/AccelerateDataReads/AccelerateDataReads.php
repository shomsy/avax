<?php

declare(strict_types=1);

namespace Avax\DataLayer\AccelerateDataReads;

final readonly class AccelerateDataReads
{
    public function __construct(
        private ChooseDataIndex      $chooseDataIndex,
        private UseReadCache         $useReadCache,
        private UseBloomFilter       $useBloomFilter,
        private ChooseReadProjection $chooseReadProjection
    ) {}

    public function accelerate(array $queryContext) : ReadAccelerationPlan
    {
        $index = $this->chooseDataIndex->choose(queryContext: $queryContext);

        $cachePlan  = $this->useReadCache->plan(queryContext: $queryContext);
        $bloomPlan  = $this->useBloomFilter->plan(queryContext: $queryContext);
        $projection = $this->chooseReadProjection->choose(queryContext: $queryContext);

        return new ReadAccelerationPlan(
            recommendedIndex: $index,
            cachePlan       : $cachePlan,
            bloomFilterPlan : $bloomPlan,
            projection      : $projection,
            estimatedSpeedup: $this->estimateSpeedup(index: $index, cache: $cachePlan, bloom: $bloomPlan)
        );
    }

    private function estimateSpeedup(ChooseDataIndex|null $index, UseReadCache $cache, UseBloomFilter $bloom) : float
    {
        $speedup = 1.0;

        if ($index !== null && $index->isUseful()) {
            $speedup *= $index->estimatedSpeedup;
        }

        if ($cache->willCache()) {
            $speedup *= $cache->estimatedHitRate > 0.5 ? 10.0 : 2.0;
        }

        if ($bloom->willUse()) {
            $speedup *= 3.0;
        }

        return $speedup;
    }

    public function toMetadata() : array
    {
        return [
            'accelerations' => [
                'index'      => $this->chooseDataIndex->describeResponsibility(),
                'cache'      => $this->useReadCache->describeResponsibility(),
                'bloom'      => $this->useBloomFilter->describeResponsibility(),
                'projection' => $this->chooseReadProjection->describeResponsibility(),
            ],
        ];
    }

    public function describeResponsibility() : string
    {
        return 'coordinates data read acceleration using indexes, caching, bloom filters, and projections.';
    }
}