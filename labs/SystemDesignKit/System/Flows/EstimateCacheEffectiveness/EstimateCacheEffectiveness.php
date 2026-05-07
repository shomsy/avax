<?php

declare(strict_types=1);

namespace Avax\Labs\SystemDesignKit\System\Flows\EstimateCacheEffectiveness;

use Avax\Labs\SystemDesignKit\System\Capabilities\Capacity\CapacityModel;

/**
 * Estimates expected cache hit/miss behavior.
 *
 * @experimental V3 labs
 */
final class EstimateCacheEffectiveness
{
    /**
     * @return array{
     *     hit_ratio: float,
     *     miss_ratio: float,
     *     hits_per_second: float,
     *     misses_per_second: float,
     *     daily_hits: float,
     *     daily_misses: float,
     *     cache_savings_ratio: string,
     * }
     */
    public function execute(CapacityModel $model) : array
    {
        $hitsPerSecond   = $model->estimatedCacheHitsPerSecond();
        $missesPerSecond = $model->estimatedCacheMissesPerSecond();
        $dailyHits       = $hitsPerSecond * 86400;
        $dailyMisses     = $missesPerSecond * 86400;

        return [
            'hit_ratio'           => $model->cacheHitRatio->target,
            'miss_ratio'          => $model->cacheHitRatio->missRatio(),
            'hits_per_second'     => $hitsPerSecond,
            'misses_per_second'   => $missesPerSecond,
            'daily_hits'          => $dailyHits,
            'daily_misses'        => $dailyMisses,
            'cache_savings_ratio' => round($model->cacheHitRatio->target * 100, 1) . '%',
        ];
    }
}
