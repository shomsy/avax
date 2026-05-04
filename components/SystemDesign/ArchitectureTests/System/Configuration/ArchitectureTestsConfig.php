<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\ArchitectureTests\System\Configuration;

final readonly class ArchitectureTestsConfig
{
    public function __construct(
        public bool $enabled = true,
    )
    {
    }
}
