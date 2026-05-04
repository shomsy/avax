<?php

declare(strict_types=1);

namespace Avax\Components\Performance\System\Configuration;

final readonly class PerformanceConfig
{
    public function __construct(
        public bool $enabled = false,
    )
    {
    }
}
