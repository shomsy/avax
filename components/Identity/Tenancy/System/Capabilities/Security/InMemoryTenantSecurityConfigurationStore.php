<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Security;

final class InMemoryTenantSecurityConfigurationStore implements TenantSecurityConfigurationStoreInterface
{
    /** @var array<string, TenantSecurityConfiguration> */
    private array $configurations = [];

    public function save(TenantSecurityConfiguration $tenantSecurityConfiguration) : void
    {
        $this->configurations[$tenantSecurityConfiguration->tenantSlug] = $tenantSecurityConfiguration;
    }

    public function find(string $tenantSlug) : TenantSecurityConfiguration|null
    {
        return $this->configurations[$tenantSlug] ?? null;
    }

    /**
     * Reset internal state for long-lived worker safety.
     */
    public function reset() : void
    {
        $this->configurations = [];
    }
}
