<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\LatencyBudget\System\PublicSurface;

final readonly class LatencyBudget
{
    public function __construct(
        public int $p50Ms = 50,
        public int $p95Ms = 100,
        public int $p99Ms = 200,
        public int $p999Ms = 500,
    )
    {
    }
}
