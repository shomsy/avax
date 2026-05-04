<?php

declare(strict_types=1);

namespace Avax\Presentation\System\Configuration;

final readonly class PresentationConfig
{
    public function __construct(
        public string $path = 'views/',
    )
    {
    }
}
