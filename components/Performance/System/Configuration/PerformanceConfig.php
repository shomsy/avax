<?php

declare(strict_types=1);

namespace Avax\Performance\System\Configuration;

final readonly class PerformanceConfig
{
    public function __construct(
        public bool $enabled = false,
    )
    {
    }
}
