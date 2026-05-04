<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Sharding\System\Configuration;

final readonly class ShardingConfig
{
    public function __construct(
        public bool $enabled = true,
    )
    {
    }
}
