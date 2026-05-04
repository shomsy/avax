<?php

declare(strict_types=1);

namespace Avax\Security\System\Configuration;

final readonly class SecurityConfig
{
    public function __construct(
        public bool $enabled = true,
    )
    {
    }
}
