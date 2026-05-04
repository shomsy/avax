<?php

declare(strict_types=1);

namespace Avax\Identity\System\Configuration;

final readonly class IdentityConfig
{
    public function __construct(
        public string $provider = 'default',
    )
    {
    }
}
