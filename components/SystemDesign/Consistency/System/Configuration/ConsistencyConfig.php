<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Consistency\System\Configuration;

final readonly class ConsistencyConfig
{
    public function __construct(
        public bool $enabled = true,
    )
    {
    }
}
