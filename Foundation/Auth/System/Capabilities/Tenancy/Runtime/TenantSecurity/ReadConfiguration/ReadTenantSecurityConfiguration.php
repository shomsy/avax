<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\TenantSecurity\ReadConfiguration;

use Avax\Auth\System\Capabilities\TenantSecurity\TenantSecurityConfiguration;
use Avax\Auth\System\Capabilities\TenantSecurity\TenantSecurityConfigurationStoreInterface;

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
