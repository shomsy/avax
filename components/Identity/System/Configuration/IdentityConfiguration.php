<?php

declare(strict_types=1);

namespace Avax\Components\Identity\System\Configuration;

final readonly class IdentityConfiguration
{
    public function __construct(
        public string $provider = 'default',
    ) {}
}
