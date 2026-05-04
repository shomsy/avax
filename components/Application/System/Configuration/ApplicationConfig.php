<?php

declare(strict_types=1);

namespace Avax\Application\System\Configuration;

final readonly class ApplicationConfig
{
    public function __construct(
        public string $environment = 'production',
    )
    {
    }
}
