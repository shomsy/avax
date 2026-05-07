<?php

declare(strict_types=1);

namespace Avax\Components\Application\Pipeline\System\Configuration;

final readonly class PipelineConfiguration
{
    public function __construct(
        public bool $haltOnFailure = true,
        public int  $maxDepth = 10,
    ) {}
}
