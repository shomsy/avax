<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\ReferenceArchitecture\System\PublicSurface;

final readonly class ReferenceArchitecture
{
    public function __construct(
        public string $name,
        public string $description,
        public array  $components = [],
    )
    {
    }
}
