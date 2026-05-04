<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\ReferenceArchitecture\System\Configuration;

final readonly class ReferenceArchitectureConfig
{
    public function __construct(
        public bool $enabled = true,
    )
    {
    }
}
