<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\LoadModel\System\PublicSurface;

final readonly class LoadModel
{
    public function __construct(
        public int   $concurrentUsers = 100,
        public int   $requestsPerUser = 10,
        public float $thinkTimeSeconds = 1.0,
        public int   $rampUpSeconds = 60,
    )
    {
    }
}
