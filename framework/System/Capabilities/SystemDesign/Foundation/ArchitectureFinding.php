<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\SystemDesign\Foundation;

final readonly class ArchitectureFinding
{
    public function __construct(
        public string $component,
        public string $classification,
        public string $risk = '',
        public string $explanation = '',
    ) {
    }
}
