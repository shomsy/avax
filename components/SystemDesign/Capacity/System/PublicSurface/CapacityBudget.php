<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Capacity\System\PublicSurface;

final readonly class CapacityBudget
{
    public function __construct(
        public int   $maxRequestsPerSecond = 10000,
        public int   $maxStorageMb = 1024000,
        public int   $maxQueueDepth = 100000,
        public int   $maxLatencyMs = 200,
        public float $availability = 0.999,
    )
    {
    }
}
