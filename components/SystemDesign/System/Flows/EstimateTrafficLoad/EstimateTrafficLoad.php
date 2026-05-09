<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\Flows\EstimateTrafficLoad;

use Avax\Components\SystemDesign\System\Capabilities\Capacity\CapacityModel;

/**
 * Calculates read/write traffic, fanout, peak traffic, burst traffic.
 *
 * @experimental V3 labs
 */
final class EstimateTrafficLoad
{
    /**
     * @return array{
     *     peak_rps: int,
     *     read_rps: int,
     *     write_rps: int,
     *     read_ratio: float,
     *     write_ratio: float,
     *     cache_hits_per_second: float,
     *     cache_misses_per_second: float,
     *     backend_rps: float,
     * }
     */
    public function execute(CapacityModel $model) : array
    {
        $peakRps     = $model->estimatedPeakRps();
        $cacheHits   = $model->estimatedCacheHitsPerSecond();
        $cacheMisses = $model->estimatedCacheMissesPerSecond();

        return [
            'peak_rps'                => $peakRps,
            'read_rps'                => $model->traffic->reads,
            'write_rps'               => $model->traffic->writes,
            'read_ratio'              => $model->traffic->readWriteRatio(),
            'write_ratio'             => $model->traffic->total > 0
                ? $model->traffic->writes / $model->traffic->total
                : 0.0,
            'cache_hits_per_second'   => $cacheHits,
            'cache_misses_per_second' => $cacheMisses,
            'backend_rps'             => $cacheMisses,
        ];
    }
}
