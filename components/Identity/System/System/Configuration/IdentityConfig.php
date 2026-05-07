<?php

declare(strict_types=1);

namespace Avax\Components\Identity\System\System\Configuration;

final readonly class IdentityConfig
{
    public function __construct(
        public string $provider = 'default',
    ) {}
}
