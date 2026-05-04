<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Partitioning\System\Configuration;

final readonly class PartitioningConfig
{
    public function __construct(
        public bool $enabled = true,
    )
    {
    }
}
