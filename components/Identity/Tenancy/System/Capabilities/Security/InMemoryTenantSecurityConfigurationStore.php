<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Security;

final class InMemoryTenantSecurityConfigurationStore implements TenantSecurityConfigurationStoreInterface
{
    /** @var array<string, TenantSecurityConfiguration> */
    private array $configurations = [];

    public function save(TenantSecurityConfiguration $configuration) : void
    {
        $this->configurations[$configuration->tenantSlug] = $configuration;
    }

    public function find(string $tenantSlug) : TenantSecurityConfiguration|null
    {
        return $this->configurations[$tenantSlug] ?? null;
    }
}
