<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Caching\System\Configuration;

final readonly class CacheConfig
{
    public function __construct(
        public bool $enabled = true,
    )
    {
    }
}
