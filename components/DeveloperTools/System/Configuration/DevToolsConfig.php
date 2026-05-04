<?php

declare(strict_types=1);

namespace Avax\DeveloperTools\System\Configuration;

final readonly class DevToolsConfig
{
    public function __construct(
        public bool $enabled = true,
    )
    {
    }
}
