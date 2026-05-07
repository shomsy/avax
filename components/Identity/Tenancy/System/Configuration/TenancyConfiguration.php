<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Configuration;

final readonly class TenancyConfiguration
{
    public function __construct(
        public string $resolverMode = 'subdomain',
        public string $defaultTenant = 'default',
        public bool   $strictIsolation = true,
    ) {}
}
