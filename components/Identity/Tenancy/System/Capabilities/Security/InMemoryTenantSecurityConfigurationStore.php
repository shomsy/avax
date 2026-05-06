<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Security;

final class InMemoryTenantSecurityConfigurationStore implements TenantSecurityConfigurationStoreInterface
{
    /** @var array<string, TenantSecurityConfiguration> */
    private array $configurations = [];

    public function save(TenantSecurityConfiguration $tenantSecurityConfiguration): void
    {
        $this->configurations[$tenantSecurityConfiguration->tenantSlug] = $tenantSecurityConfiguration;
    }

    public function find(string $tenantSlug): ?TenantSecurityConfiguration
    {
        return $this->configurations[$tenantSlug] ?? null;
    }
}
