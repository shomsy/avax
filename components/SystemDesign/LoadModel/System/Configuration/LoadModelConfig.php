<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\LoadModel\System\Configuration;

final readonly class LoadModelConfig
{
    public function __construct(
        public bool $enabled = true,
    )
    {
    }
}
