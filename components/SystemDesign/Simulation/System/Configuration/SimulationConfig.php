<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Simulation\System\Configuration;

final readonly class SimulationConfig
{
    public function __construct(
        public bool $enabled = true,
    )
    {
    }
}
