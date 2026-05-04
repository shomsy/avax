<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\ArchitectureTests\System\PublicSurface;

final readonly class ArchitectureTest
{
    public function __construct(
        public string $name,
        public bool   $passed,
        public string $message = '',
    )
    {
    }
}
