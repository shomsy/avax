<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Simulation\System\PublicSurface;

final readonly class SimulationConfig
{
    public function __construct(
        public int  $iterations = 1000,
        public int  $warmup = 100,
        public bool $parallel = false,
    )
    {
    }
}
