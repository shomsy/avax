<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Capacity\System\Configuration;

final readonly class CapacityConfig
{
    public function __construct(
        public bool $enabled = true,
    )
    {
    }
}
