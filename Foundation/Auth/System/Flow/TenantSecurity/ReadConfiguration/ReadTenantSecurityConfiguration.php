<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\TenantSecurity\ReadConfiguration;

use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityConfiguration;
use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityConfigurationStoreInterface;

final readonly class ReadTenantSecurityConfiguration
{
    private TenantSecurityConfigurationStoreInterface $configurationStore;

    public function __construct(
        TenantSecurityConfigurationStoreInterface $configurationStore
    )
    {
        $this->configurationStore = $configurationStore;
    }

    public function execute(string $tenantSlug) : TenantSecurityConfiguration|null
    {
        return $this->configurationStore->find(tenantSlug: $tenantSlug);
    }
}
