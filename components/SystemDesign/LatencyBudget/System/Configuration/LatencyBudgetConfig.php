<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\LatencyBudget\System\Configuration;

final readonly class LatencyBudgetConfig
{
    public function __construct(
        public bool $enabled = true,
    )
    {
    }
}
