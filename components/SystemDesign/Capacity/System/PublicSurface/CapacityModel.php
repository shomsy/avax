<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Capacity\System\PublicSurface;

final readonly class CapacityModel
{
    public function __construct(
        public int   $requestsPerSecond = 0,
        public int   $readsPerSecond = 0,
        public int   $writesPerSecond = 0,
        public float $readWriteRatio = 1.0,
        public int   $peakTrafficMultiplier = 1,
        public int   $burstWindow = 60,
        public int   $fanoutSize = 1,
        public int   $averageObjectSize = 1024,
        public int   $retentionDays = 30,
        public float $cacheHitRatio = 0.0,
        public int   $queueDepth = 0,
        public int   $consumerCount = 1,
    )
    {
    }
}
