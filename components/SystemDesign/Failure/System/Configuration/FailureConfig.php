<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Failure\System\Configuration;

final readonly class FailureConfig
{
    public function __construct(
        public bool $enabled = true,
    )
    {
    }
}
