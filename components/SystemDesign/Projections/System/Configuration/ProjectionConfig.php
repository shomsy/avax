<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Projections\System\Configuration;

final readonly class ProjectionConfig
{
    public function __construct(
        public bool $enabled = true,
    )
    {
    }
}
