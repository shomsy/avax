<?php

declare(strict_types=1);

namespace Avax\Components\Application\System\Configuration;

final readonly class ApplicationConfig
{
    public function __construct(
        public string $environment = 'production',
    )
    {
    }
}
